<?php
namespace admin\library\mvc\comp\spl\objItem\help;

// ORM
use ORM\comp\spl\objItem\article\article as articleOrm;
use ORM\tree\compContTree;

/**
 * Description of article
 *
 * @author Козленко В.Л.
 */
trait common {


    public function saveDataInfo($pObjItemId, $pObjItemOrm){
        $objItemData = $pObjItemOrm
            ->select('i.id, i.seoUrl, i.treeId, i.caption, a.prevImgUrl, i.isPublic, a.divArticle, a.isPrivate'
                         . ',cc.seoName, cc.name category, a.isCloaking'
                         . ',DATE_FORMAT(i.date_add, "%Y-%m-%dT%h:%i+04:00") as dateISO8601'
                         . ',DATE_FORMAT(i.date_add, "%d.%m.%y %H:%i") date_add, i.date_add dateunf, a.urlTpl', 'i')
            ->joinLeftOuter(articleOrm::TABLE. ' a', 'i.id=a.objItemId')
            ->join(compContTree::TABLE . ' cc', 'i.treeId=cc.id')
            ->where('i.id=' . $pObjItemId)
            ->comment(__METHOD__)
            ->fetchFirst();

        //var_dump($objItemData);
        //exit;

        $return['prev'] = $pObjItemOrm
            ->select('i.id, i.seoUrl, i.caption, cc.seoName, a.urlTpl', 'i')
            ->joinLeftOuter(articleOrm::TABLE. ' a', 'i.id=a.objItemId')
            ->join(compContTree::TABLE . ' cc', 'i.treeId=cc.id')
            ->where(
            'date("' . $objItemData['dateunf'] . ' ") >= date(i.date_add)
                AND i.isPublic = "yes"
                AND i.isDel = 0
                And i.treeId = ' . $objItemData['treeId'] . '
                AND i.id < ' . $objItemData['id'])
            ->order('i.date_add DESC, i.id desc')
            ->fetchFirst();

        $return['next'] = $pObjItemOrm
            ->select('i.id, i.seoUrl, i.caption, cc.seoName, a.urlTpl', 'i')
            ->joinLeftOuter(articleOrm::TABLE. ' a', 'i.id=a.objItemId')
            ->join(compContTree::TABLE . ' cc', 'i.treeId=cc.id')
            ->where(
            'date("' . $objItemData['dateunf'] . ' ") <= date(i.date_add)
                AND i.isPublic = "yes"
                AND i.isDel = 0
                And i.treeId = ' . $objItemData['treeId'] . '
                AND i.id > ' . $objItemData['id'])
            ->order('i.date_add ASC')
            ->fetchFirst();

        $objItemData['canonical'] = sprintf($objItemData['urlTpl'], $objItemData['seoName'], $objItemData['seoUrl']);

        unset($objItemData['seoUrl'], $objItemData['urlTpl'], $objItemData['treeId'], $objItemData['dateunf']);

        $return['obj'] = $objItemData;

        return $return;
        // func. saveDataInfo
    }

    // trait common
}