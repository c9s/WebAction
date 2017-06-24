<?php
namespace WebAction\ActionTemplate;

use WebAction\ActionLoader;
use WebAction\Exception\RequiredConfigKeyException;
use WebAction\ActionRunner;
use WebAction\RecordAction\UpdateOrderingRecordAction;

/**
 *  Update Ordering Record Action Template Synopsis
 *
 *    $actionTemplate = new UpdateOrderingRecordActionTemplate;
 *    $loader = new WebAction\ActionRunner;
 *    $actionTemplate->register($loader, 'UpdateOrderingRecordActionTemplate', array(
 *        'namespace' => 'test2',
 *        'model' => 'Test2Model',   // model's name
 *    ));
 *
 *    $className = 'test2\Action\SortTest2Model';
 *    $actionArgs = $loader->pretreatments[$className]['actionArgs'];
 *    $generatedAction = $actionTemplate->generate($className, $actionArgs);
 *
 *    $generatedAction->load();
 */
class UpdateOrderingRecordActionTemplate extends RecordActionTemplate
{
    public function register(ActionLoader $loader, $asTemplate, array $options = array())
    {
        if (isset($options['use'])) {
            array_unshift($options['use'], '\\WebAction\\Action', '\\WebAction\\RecordAction\\BaseRecordAction');
        } else {
            $options['use'] = ['\\WebAction\\Action', '\\WebAction\\RecordAction\\BaseRecordAction'];
        }


        if (!isset($options['model'])) {
            if (isset($options['record_class'])) {
                $nslist = explode("\\Model\\", $options['record_class']);
                $options['model'] = $nslist[1];

                if (!isset($options['namespace'])) {
                    $options['namespace'] = $nslist[0];
                }
            } else {
                throw new RequiredConfigKeyException('model', 'required for creating record actions');
            }
        }

        if (!isset($options['namespace'])) {
            throw new RequiredConfigKeyException('namespace', 'namespace');
        }

        $actionClass = $options['namespace'] . '\\Action\\Update' . $options['model'] . 'Ordering';
        $loader->register($actionClass, $asTemplate, [
            'extends' => "\\WebAction\\RecordAction\\UpdateOrderingRecordAction",
            'properties' => [
                'recordClass' => $options['namespace'] . "\\Model\\" . $options['model'],
            ]
        ]);
    }
}
