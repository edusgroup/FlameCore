<?php

namespace admin\library\mvc\comp\spl\article;

// ORM
use ORM\comp\spl\article\article as articleOrm;
use ORM\tree\compContTree;
use ORM\imgSizeList;
// Model
use admin\library\mvc\manager\complist\model as complistModel;
// Engine
use core\classes\DB\tree;
use core\classes\word;
use core\classes\filesystem;
use core\classes\validation\filesystem as fileValid;
use core\classes\component\abstr\admin\comp as compAbs;
use core\classes\event as eventCore;
// Conf
use \DIR;

class model {

    public static function getList(integer $pContId) {
        $articleOrm = new articleOrm();
        // {select},{edit},Заголовок,СЕО URL, Публиковать
        $list = $articleOrm->selectAll('id, 0, "", caption, if(trim(seoUrlTmp)="",seoUrl, seoUrlTmp) as seoUrl,'
                                        .'if(isPublic="yes", 1, 0) isPublic',
                                       ['treeId' => $pContId, 'isDel' => 0], 'date_add desc, id desc');
        return ['body' => $list ];
        // func. getList
    }

    public static function getSizeList(integer $contId) {
        $imgSizeList = new imgSizeList();
        $compContTree = new compContTree();
        // TODO: Заменить на treeUrl у article
        $contList = $compContTree->getTreeUrlById(compContTree::TABLE, $contId);
        foreach ($contList as $item) {
            $list = $imgSizeList->selectAll('*', 'contid=' . $item['id']);
            if ($list) {
                return $list;
            } // if
        } // foreach
        // func. getSizeList
    }

    /**
     * Формирует список размеров изображений
     * @static
     * @param $pSizeList
     * @return array|null
     */
    public static function makeSelect($pSizeList) {
        if (!$pSizeList) {
            return null;
        }
        $return = array();
        $return[] = array('id' => -1, 'name' => 'Исходное');
        foreach ($pSizeList as $item) {
            $name = $item['name'] . '(' . $item['type'] . ':' . $item['val'] . ')';
            $return[] = ['id' => $item['id'],
                         'name' => $name];
        }
        return $return;
        // func. makeSelect
    }

    public static function isDublFile($pFileTmpName, $pContId) {
        // Создаем ORM
        $contFile = new contFile();
        // Получаем md5 суммуы
        $fileMd5 = md5_file($pFileTmpName);
        // Записываем или смотрим есть ли запись
        $where = ['md5' => $fileMd5, 'contid' => $pContId];
        $isNew = $contFile->save($where, $where);
        return $isNew === null;
        // func. isDublFile
    }

    public static function getPath($pCompId, $pContId, $pArticleId) {
        return 'comp/' . $pCompId . '/' . $pContId . '/' . word::idToSplit($pArticleId);
        // func. getPath
    }

    public static function fileRm($pCondId, $pCompId, $pArtId, $pNameList) {
        //$contFile = new contFile();
        //$where['contid'] = $pCondId;

        $pathPrefix = self::getPath($pCompId, $pCondId, $pArtId);

        $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;
        $filePreviewPath = DIR::getPreviewImgPath($pathPrefix);
        $fileResizePath = DIR::getSiteImgResizePath() . $pathPrefix;

        foreach ($pNameList as $name) {
            fileValid::isSafe($name, new \Exception('Неверное имя файла', 234));

            //$where['md5'] = md5_file($pathDist.$name);
            //$contFile->delete($where);

            filesystem::unlink($fileDistPath . $name);
            filesystem::unlink($filePreviewPath . $name);
            //filesystem::rUnlink($filePreviewPath, filesystem::ALL_NO_FILTER_FOLDER, $name);
            filesystem::rUnlink($fileResizePath, filesystem::ALL_NO_FILTER_FOLDER, $name);
        }
        // func. fileRm
    }

    public static function saveTableItemData($pData, $pContId) {
        $return = array();
        // Парсим данны
        $data = json_decode($pData, true);
        if ($data) {
            $articleOrm = new articleOrm();

            // Бегаем по данным
            foreach ($data as $item) {
                if (!isset($item['id'])) {
                    throw new \Exception('Неверный JSON', 234);
                }
                $id = (int) $item['id'];
                $saveData = [];
                if (isset($item['data']['caption'])) {
					if ( !$item['data']['caption']){
						throw new \Exception('Заголовок не может быть пустым', 239);
					}
                    $saveData['caption'] = $item['data']['caption'];
                }
                if (isset($item['data']['seoUrl']) && $item['data']['seoUrl']) {
                    $saveData['seoUrlTmp'] = $item['data']['seoUrl'];
                }
				if ( !isset($saveData['seoUrlTmp'])){
				    $caption = $articleOrm->get('caption', 'id='.$id);
					$saveData['seoUrlTmp'] = word::wordToUrl($caption);
				} // if
				// if isset seoUrl
                if (isset($item['data']['isPublic'])) {
                    $isPublic = (int) $item['data']['isPublic'];
                    $saveData['isPublic'] = $isPublic ? 'yes' : 'no';
                }
                $saveData['treeId'] = $pContId;
                $newId = $articleOrm->save(['id'=>$id], $saveData);

                eventCore::callOffline(
                    event::NAME,
                    event::ACTION_TABLE_SAVE,
                    ['contId'=>$pContId],
                    $newId ? : $id
                );

                $return[$id] =  $newId ? : $id;;
            }// foreach
        }
        return $return;

        // func. saveTableItemData
    }

// class model
}

?>