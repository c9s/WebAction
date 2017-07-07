<?php
namespace WebAction\RecordAction;

use WebAction\Action;
use Exception;

abstract class SortRecordAction extends Action
{
    const MODE_INCREMENTALLY = 1;
    const MODE_BYDATE = 2;

    public $mode = self::MODE_INCREMENTALLY;

    /**
     * @var string the target model class.
     */
    public $recordClass;


    /**
     * @var string your model schema must provide the column for
     *             storing ordering data.
     */
    public $targetColumn = 'ordering';


    public function schema()
    {
        $this->param('keys');
    }


    public function loadRecord($key)
    {
        return $this->recordClass::findByPrimaryKey($key);
    }

    public function runUpdateList()
    {
        if ($this->mode !== self::MODE_INCREMENTALLY) {
            throw new Exception("Unsupported sort mode");
        }

        if ($keys = $this->arg('keys')) {
            $cnt = 0;
            foreach ($keys as $key) {
                $record = $this->loadRecord($key);
                if (!$record) {
                    throw new Exception("Record not found.");
                }

                $ret = $record->update([ $this->targetColumn => $cnt++ ]);
                if ($ret->error) {
                    throw new Exception("Record update failed: {$ret->message}");
                }
            }
        }
    }

    public function run()
    {
        try {
            $this->runUpdateList();
        } catch (Exception $e) {
            return $this->error("Ordering Update Failed: {$e->getMessage()}");
        }
        return $this->success('排列順序已更新');
    }
}
