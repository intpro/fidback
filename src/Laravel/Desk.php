<?php

namespace Interpro\Fidback\Laravel;
use Illuminate\Support\Facades\Bus;
use Interpro\Fidback\Concept\Desk as DeskInterface;
use Interpro\QuickStorage\Concept\Command\CreateGroupItemCommand;
use Interpro\QuickStorage\Concept\Command\UpdateGroupItemCommand;
use Interpro\QuickStorage\Concept\Repository;
use Interpro\QuickStorage\Laravel\Item\GroupItem;

class Desk implements DeskInterface
{
    private $repository;

    public function __construct(
        Repository $repository
    ){
        $this->repository = $repository;
    }

    /**
     * @param string $type_name
     *
     * @param array $data_arr
     *
     */
    public function registerMessage($type_name, $data_arr)
    {
        $block_name = config('fidback')['block_name'];

        $dataArr = Bus::dispatch(new CreateGroupItemCommand($block_name, $type_name, 0));

        Bus::dispatch(new UpdateGroupItemCommand($dataArr['id'], $data_arr));

        //Нужна фабрика для GroupItem, это временное решение
        $item_arr = $this->repository->getGroupItem($block_name, $type_name, $dataArr['id']);
        $group_item = new GroupItem($item_arr);

        return $group_item;

    }

}
