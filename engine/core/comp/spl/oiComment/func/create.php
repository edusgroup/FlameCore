<?php

namespace core\comp\spl\oiComment\func;

// Core
use core\classes\request;
use core\classes\render;
use core\classes\word;
use core\classes\filesystem;

// ORM
use ORM\comp\spl\oiComment\oiCommentBi as oiCommentBiOrm;
use ORM\comp\spl\oiComment\oiCommentProp as oiCommentpPropOrm;
use ORM\comp\spl\oiComment\oiComment as oiCommentOrm;
use ORM\tree\componentTree as componentOrm;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\blockItemSettings;
use core\classes\site\dir as sitePath;
use ORM\users as usersOrm;

// Conf
use \site\conf\DIR;
use \site\conf\SITE;

/**
 * Description of create
 *
 * @author Козленко В.Л.
 */
class create {

	const COMP_OICOMPONENT_ID = 11;

    private function makeList($id, $comList, &$data) {

        $comListCount = count($comList);
        $flag = false;
        for ($i = 0; $i < $comListCount; $i++) {
            if ($comList[$i]['tree_id'] != $id && $flag) {
                break;
            } // if $flag
            if ($comList[$i]['tree_id'] == $id) {
                $itemId = $comList[$i]['id'];
                $data .= ',' . $itemId;
                self::makeList($itemId, $comList, $data);
                $flag = true;
            }
        } // for
        // func. makeList
    }

    public function save() {
        $blockItemId = request::getInt('blockItemId');
        $objItemId = request::getInt('objItemId');

        // Текст комментария
        $comment = request::postSafe('comment');
        $author = request::postSafe('author');
        // Если ни чего нет и
        if ( !$comment ){
            return;
        }

        //$commentPostId = request::postInt('comment_post_ID');
        // ID родителя комментария, т.е. к какому комментарию он относится
        $parentId = request::postInt('parentId');

        // Получаем настроечные данные
        $oiCommentBiOrm = new oiCommentBiOrm();
        $oiCommentData = $this->_getOiCOmmentData($blockItemId);
		
        // Название шаблона для комментариев
        // TODO: Сделать обработку внешних шаблонов. Убрать [o],
        // TODO: внизу заменить sitePath::getSiteCompTplPath(false, на sitePath::getSiteCompTplPath($isOut,
        $tplListFile = substr($oiCommentData['tplListFile'], 1);
        $tplComFile = substr($oiCommentData['tplComFile'], 1);
        // Тип комментариев
        $oiCommentType = $oiCommentData['type'];

         $oiCommentOrm = new oiCommentOrm();
         $comList = $oiCommentOrm->selectFirst(
             'id',
             ['type' => $oiCommentType, 'objId' => $objItemId]
         );
         $isFirst = !(boolean)$comList;
         unset($comList);

         $userId = isset($_SESSION['userData']['id']) ? (int)$_SESSION['userData']['id'] : null;
         $previewUrl = (new usersOrm())->get('previewurl', ['id'=>$userId]);

         $oiCommentOrm->insert(['userName' => $author,
                            'comment' => $comment,
                            'tree_id' => $parentId,
                            'type' => $oiCommentType,
                            'objId' => $objItemId,
                            'userId' => $userId
                            ]);
        $newId = $oiCommentOrm->insertId();

        // ======= Создаём код Комментария
        // Преобразуем NameSpace имя в путь папки
        $nsPath = filesystem::nsToPath($oiCommentData['ns']);
        $tplPath = sitePath::getSiteCompTplPath(false, $nsPath);
        // Создаём шаблон
        $render = new render($tplPath, '');
        $render->setMainTpl($tplComFile)
            ->setVar('author', $author)
            ->setVar('comment', $comment)
            ->setVar('id', $newId)
            ->setVar('isFirst', $isFirst)
            ->setVar('previewurl', $previewUrl)
            ->setVar('dateAdd', date("d-m-y H:i"))
            ->render();

	   $this->updateFile($render, $oiCommentOrm, $objItemId, $oiCommentType, $tplListFile, $tplComFile, $oiCommentData['compId']);
        // func. save
    }
	
	protected function _getOiCOmmentData($blockItemId){
		// Получаем настроечные данные
        $oiCommentBiOrm = new oiCommentBiOrm();
        $oiCommentData = $oiCommentBiOrm
            ->select('acb.tplListFile, acb.tplComFile, acp.type, c.ns, c.id as compId', 'acb')
            ->join(blockItemSettings::TABLE . ' bis', 'bis.blockItemId=acb.blockItemId')
            ->join(oiCommentpPropOrm::TABLE . ' acp', 'acp.contId=bis.custContId')
            ->join(compContTreeOrm::TABLE . ' cc', 'cc.id = bis.custContId')
            ->join(componentOrm::TABLE . ' c', 'c.id = cc.comp_id')
            ->where('acb.blockItemId=' . $blockItemId)
            ->comment(__METHOD__)
            ->fetchFirst();
        if (!$oiCommentData) {
            throw new \Exception('No data from blockItemId=' . $blockItemId);
        }
		return $oiCommentData;
		// func. _getOiCOmmentData
	}
	
	public function delete(){
		$commenId = request::postInt('commentId');
	
		$blockItemId = request::getInt('blockItemId');
		$oiCommentData = $this->_getOiCOmmentData($blockItemId);
		
		$objItemId = request::getInt('objItemId');
		
		$oiCommentType = $oiCommentData['type'];
		
		$tplListFile = substr($oiCommentData['tplListFile'], 1);
        $tplComFile = substr($oiCommentData['tplComFile'], 1);	
	
		$nsPath = filesystem::nsToPath($oiCommentData['ns']);
        $tplPath = sitePath::getSiteCompTplPath(false, $nsPath);
	
	
		$oiCommentOrm = new oiCommentOrm();
		$oiCommentOrm->delete(['id'=>$commenId]);
		
		$render = new render($tplPath, '');
		
		return $this->updateFile($render, $oiCommentOrm, $objItemId, $oiCommentType, $tplListFile, $tplComFile, $oiCommentData['compId']);
		// func. delete
	}

    public function updateFile($render, $oiCommentOrm, $objItemId, $oiCommentType, $tplListFile, $tplComFile, $compId) {
	
	 // Формируем правильное дерево
       $comList = $oiCommentOrm->selectAll(
            'id, tree_id',
            ['type' => $oiCommentType, 'objId' => $objItemId],
            'tree_id, date_add');
			
		if ( $comList){
			$idString = '';
			// Получаем правильную последовательность ID
			self::makeList(0, $comList, $idString);
			$idString = substr($idString, 1);
		}else{
			$idString = -1;
		}
		unset($comList);
        

        // ======= Создаём код Списка комментариев и сохраняем в файл
        $comListHandle = $oiCommentOrm->query('
            SELECT cc.*, cc.tree_id as treeId, DATE_FORMAT(cc.date_add, "%y-%m-%d") dateAdd, DATE_FORMAT(cc.date_add, "%d-%m-%y %H:%i")  dateAddSys, u.previewurl
            FROM pr_comp_oicomment cc
            LEFT JOIN  pr_users u ON u.id = cc.userId
            WHERE cc.id in (' . $idString . ')
            ORDER BY field(cc.id, ' . $idString . ')
        ');


        ob_start();
        $render->setMainTpl($tplListFile)
            ->clear()
            ->setVar('comHandle', $comListHandle)
            ->setBlock('comment', $tplComFile)
            ->render();

        $data = ob_get_clean();
        $objItemId = word::idToSplit($objItemId);
        $folder = DIR::APP_DATA . 'comp/' . $compId . '/' . $oiCommentType . '/' . $objItemId;
        filesystem::saveFile($folder, 'comm.html', $data);
		
		return ['status'=>'ok'];
        // func. updateFile
    }

    // func. createArtCom
}