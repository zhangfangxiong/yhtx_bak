<?php

/**
 *
 * @author xiejinci
 *
 */
class Model_Category extends Model_Base
{

    const DB_NAME = 'yhtx';

    const TABLE_NAME = 'goods_category';

    protected static $aRootTree = [];

    protected static $aTree = [];

    //获取所有数据(key为数组的key)
    public static function getAllTree($sKey = '')
    {
        //获取库中所有有效数据
        $aWhere = array(
            'iStatus' => 1,
        );
        $aList = self::getAll(array(
            'where' => $aWhere,
            'order' => 'iID ASC'
        ));
        if ($sKey) {
            $aTmp = [];
            foreach ($aList as $key => $value) {
                $aTmp[$value[$sKey]] = $value;
            }
            $aList = $aTmp;
        }
        return $aList;
    }

    //获取顶级分类
    public static function getRootTree($aList)
    {
        if (!empty(self::$aRootTree)) {
            //按照父ID分组
            $aParents = array();
            foreach ($aList as $key => $value) {
                if ($value['iParentID'] == 0) {
                    $aParents[$value['iID']] = $value;
                    //unset($aList[$key]);
                }
            }
            self::$aRootTree = $aParents;
        }
        return self::$aRootTree;
    }

    //获取树状menu
    public static function getTree($aList)
    {
        $aRoot = self::getRootTree($aList);
        while (count($aRoot) != count($aList)) {
            foreach ($aList as $key => $value) {
                if (isset($aList[$value['iParentID']])) {
                    $aList[$value['iParentID']]['aSon'][] = $value;
                    unset($aList[$key]);
                }
            }
            self::getTree($aList);
        }

        return $aList;
    }

    public static function exsistCategory($sName, $iParent)
    {
        $aWhere = array(
            'sName' => $sName,
            'iParent' => $iParent,
        );
        $aList = self::getRow(array(
            'where' => $aWhere,
        ));
        return $aList;
    }
}