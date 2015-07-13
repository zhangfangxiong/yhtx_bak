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

    //获取树状menu
    public static function getTree($iUserID = 0)
    {
        //获取库中所有有效数据
        $aWhere = array(
            'iStatus' => 1,
        );
        $aList = self::getAll(array(
            'where' => $aWhere,
            'order' => 'iID ASC'
        ));
        //按照父ID分组
        $aParents = array();//一级菜单列表
        foreach ($aList as $key => $value) {
            if ($value['iParentID'] == 0) {
                $aParents[$value['iID']] = $value;
                unset($aList[$key]);
            }
        }
        //整合二级菜单
        foreach ($aList as $key => $value) {
            if (isset($aParents[$value['iParentID']])) {
                $aParents[$value['iParentID']]['aSons'][$value['iID']] = $value;
                unset($aList[$key]);
                //整合三级菜单
                foreach ($aList as $k => $v) {
                    if ($v['iParentID'] == $value['iID']) {
                        $aParents[$value['iParentID']]['aSons'][$value['iID']]['aSons'][] = $v;
                        unset($aList[$k]);
                    }
                }
            }
        }
        return $aParents;
    }
}