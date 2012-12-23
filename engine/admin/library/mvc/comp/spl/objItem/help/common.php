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

   /* Настройка метода для buildsys\library\event\comp\spl\objItem\article\model

    */
    public function saveDataInfo($pObjItemId, $pObjItemOrm){
        $objItemData = $pObjItemOrm
            ->select('i.id, i.seoUrl, i.treeId, i.caption, a.prevImgUrl, i.isPublic'
                         . ',cc.seoName, cc.name category, a.seoKeywords, a.seoDescr, a.isCloaking'
                         . ',DATE_FORMAT(i.date_add, "%Y-%m-%dT%h:%i+04:00") as dateISO8601'
                         . ',DATE_FORMAT(i.date_add, "%d.%m.%y %H:%i") date_add, i.date_add dateunf, a.urlTpl', 'i')
            ->joinLeftOuter(articleOrm::TABLE. ' a', 'i.id=a.objItemId')
            ->join(compContTree::TABLE . ' cc', 'i.treeId=cc.id')
            ->where('i.id=' . $pObjItemId)
            ->comment(__METHOD__)
            ->fetchFirst();

        // Данные предыдушей статьи
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

        // Данные следующей статьи
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

    // trait table
}