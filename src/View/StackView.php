<?php
namespace WebAction\View;

use WebAction\View\BaseView;
use FormKit;
use FormKit\Element;
use FormKit\Widget\Label;
use FormKit\Widget\HiddenInput;
use FormKit\Widget\ButtonInput;
use FormKit\Widget\SubmitInput;
use FormKit\Widget\CheckboxInput;
use FormKit\Layout\GenericLayout;
use Maghead\Schema\DeclareSchema;
use Maghead\Schema\Relationship\Relationship;
use Maghead\Schema\Relationship\HasOne;
use Maghead\Schema\Relationship\HasMany;
use Maghead\Schema\Relationship\BelongsTo;
use Maghead\Schema\Relationship\ManyToMany;
use Maghead\Runtime\Model;
use Maghead\Runtime\Collection;

/**
 *  $view = new StackView( $action, array(
 *      'no_form' => true,
 *      'no_signature' => true,
 *      'form_id' => 'formId',
 *      'form_class' => 'product-form',
 *  ));
 *  $view->buildRelationalActionViewForExistingRecords($relationId, $relation);
 *  $view->buildRelationalActionViewForNewRecord($relationId,$relation);
 */

class StackView extends BaseView
{
    public $ajax = false;

    public function setAjax($ajax)
    {
        $this->ajax = $ajax;
    }

    /**
     * Create Layout Container object.
     */
    public function createContainer()
    {
        $container = parent::createContainer();
        if ($this->option('no_form')) {
            return $container;
        } else {
            if ($this->ajax) {
                $ajaxFlag  = new HiddenInput('__ajax_request', array( 'value' => '1' ));
                $container->append($ajaxFlag);
                $container->addClass('ajax-action');
            }
        }
        return $container;
    }




    public function createRelationalActionViewForNewRecord($relationId, $relation)
    {
        // get the record class.
        $foreignSchema = new $relation['foreign_schema'];
        $recordClass = $foreignSchema->getModelClass();
        $record = new $recordClass;

        $subview = $this->createRelationalActionView($relationId, $relation);
        $html    = addslashes($subview->render());

        // here we create a button to insert the view from javascript.
        $button  = new ButtonInput;
        $button->value = _('Add') . $record->getLabel();
        $button->onclick = <<<SCRIPT
            var self = this;
            var el = document.createElement('div');
            var closeBtn = document.createElement('input');
            closeBtn.type = 'button';
            closeBtn.value = '移除';
            closeBtn.onclick = function() {
                self.parentNode.removeChild(el);
            };
            el.innerHTML = '$html';
            el.appendChild( closeBtn );
            this.parentNode.insertBefore(el, this.nextSibling);
SCRIPT;
        return $button;
    }


    /**
     * build ManyToMany RelationalActionView For existing junctional records
     *
     * @param $record     Model         the main record
     * @param $relationId string            the relationship id
     * @param $relation   array             the relationship data.
     * @param $subset     BaseCollection    the subset collection (junction data, for rendering the checked items)
     * @param $collection BaseCollection    the collection (for rendering the list)
     */
    public function buildManyToManyRelationalActionViewForExistingRecords($record, $relationId, $relation = null, $subset = null, $collection = null)
    {
        // Our default view for ManyToMany relationship
        if (! $relation) {
            $relation = $this->action->getRelation($relationId);
        }
        $view  = isset($relation['view']) ? new $relation['view'] : new \WebAction\View\ManyToManyCheckboxView;
        return $view->render($relationId, $record, $subset, $collection);
    }

    public function renderManyToManyEditor($record, $relationId, $superset)
    {
        $view  = isset($relation['view']) ? new $relation['view'] : new \WebAction\View\ManyToManyCheckboxView;
        return $view->render($relationId, $record, $superset);
    }


    /**
     * For each existing (one-many) records,
     * create it's own subaction view for these existing
     * records.
     */
    public function buildOneToManyRelationalActionViewForExistingRecords($record, $relationId, $relation = null)
    {
        if (! $relation) {
            $relation = $this->action->getRelation($relationId);
        }

        $container = new Element('div');

        // If the record is loaded and the relation is defined
        if ($collection = $record->fetchOneToManyRelationCollection($relationId)) {
            foreach ($collection as $subrecord) {
                $subview = $this->createRelationalActionView($relationId, $relation, $subrecord);
                $container->append($subview);
            }
        }
        return $container;
    }

    public function buildRelationalActionViewForExistingRecords($relationId, $relation = null)
    {
        if (! $relation) {
            $relation = $this->action->getRelation($relationId);
        }

        // get record from action
        $record = $this->getRecord();
        $container = $this->getContainer();

        // handle HAS_MANY records
        if ($relation instanceof HasMany) {
            $contentContainer = $this->buildOneToManyRelationalActionViewForExistingRecords($record, $relationId, $relation);
            $contentContainer->appendTo($container);
        } else if ($relation instanceof ManyToMany) {
            $contentContainer = $this->buildManyToManyRelationalActionViewForExistingRecords($record, $relationId, $relation  /* $subset, $collection */);
            $contentContainer->appendTo($container);
        }
        return $container;
    }



    public function buildRelationalActionViewForNewRecord($relationId, $relation = null)
    {
        if (! $relation) {
            $relation = $this->action->getRelation($relationId);
        }

        // create another subview for creating new (one-many) record.
        // currently onlly for HAS_MANY relationship
        $container = $this->getContainer();

        if ($relation instanceof HasMany) {
            $addButton = $this->createRelationalActionViewForNewRecord($relationId, $relation);
            $container->append($addButton);
        }
        return $container;
    }


    /**
     * See if we can build subactions and render it outside of an action view.
     *
     * @param View $container The container view.
     */
    public function buildNestedSection()
    {

        // in current action, find all relationship information, and iterate
        // them.
        foreach ($this->action->relationships as $relationId => $relation) {
            // ski$p non-renderable relationship definitions
            if (isset($relation['renderable']) && $relation['renderable'] === false) {
                continue;
            }
            $this->buildRelationalActionViewForExistingRecords($relationId, $relation);

            // currently we only support rendering new record form for "has many"
            if ($relation instanceof HasMany) {
                $this->buildRelationalActionViewForNewRecord($relationId, $relation);
            }
        }
    }


    public function build()
    {
        $container = $this->getContainer();

        $widgets = $this->getAvailableWidgets();
        $this->registerWidgets($widgets);

        // Render relationships in the same form
        // If attribute 'nested' is defined.
        if ($this->action->nested && $this->renderNested) {
            $this->buildNestedSection();
        }

        // if we use form
        $record = $this->getRecord();
        $recordId = $record ? $record->id : null;

        // if we don't have form, we don't need submit button and action signature.
        if (! $this->option('no_form')) {

            // Add control buttons
            $container->append(new SubmitInput);

            // if we have record and the record has an id, render the id field as hidden field.
            if (! $this->option('no_signature')) {
                $container->append($this->action->createSignatureWidget());
            }
        }
        return $container;
    }


    /**
     * As to handle record relationship, we need to render
     * subactions inside the current action.
     *
     * Here we use the action view without the form element wrapper.
     * Then did a small trick to the field name, e.g.
     *
     * subaction[name] => form[subaction][0][name]
     *
     * Currently this is only for one-many relationship.
     *
     * @param string $relationId
     * @param array  $relation
     * @param \Phifty\Model $record
     */
    public function createRelationalActionView($relationId, $relation, $record = null)
    {
        if (! $record) {
            $foreignSchema = new $relation['foreign_schema'];
            $recordClass = $foreignSchema->getModelClass();
            $record      = new $recordClass;
            $action      = $record->asCreateAction();
        } else {
            $action      = $record->asUpdateAction();
        }

        // rewrite the field names with index, so that we will get something like:
        //
        //    categories[index][name]...
        //    categories[index][subtitle]...
        //
        $formIndex = $action->setParamNamesWithIndex($relationId);

        $viewClass = isset($relation['view']) ? $relation['view'] : 'WebAction\View\StackView';

        $subview = new $viewClass($action, array(
            'no_form' => 1,
            'ajax' => $this->ajax
        ));

        $subview->triggerBuild();
        $container = $subview->getContainer();
        $signature = new HiddenInput("{$relationId}[{$formIndex}][__action]", array(
            'value' => $action->getSignature()
        ));
        $container->append($signature);

        $token = $action->getCSRFToken();
        if ($token) {
            $csrfToken = new HiddenInput("{$relationId}[{$formIndex}][_csrf_token]", array(
                'value' => $token
            ));
            $container->append($csrfToken);
        }
        return $subview;
    }
}
