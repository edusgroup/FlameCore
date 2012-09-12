<?php
namespace admin\library\mvc\comp\spl\objItem\help\model\table;

// ORM
use ORM\comp\spl\objItem\objItem as objItemOrm;
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

// Event
use admin\library\mvc\comp\spl\objItem\help\event\base\event;

class model {

    /**
     * Сохранение названия, seoUrl, публикации из таблицы itemObj
     * @static
     * @param $pData
     * @param $pContId
     * @return array
     * @throws \Exception
     */
    public static function saveTableItemData($pData, $pContId) {
        $return = [];
        // Парсим данны
        $data = json_decode($pData, true);
        if ($data) {
            $objItemOrm = new objItemOrm();

            // Бегаем по данным
            foreach ($data as $item) {
                if (!isset($item['id'])) {
                    throw new \Exception('Неверный JSON', 234);
                }
                $id = (int)$item['id'];
                $saveData = [];
                if (isset($item['data']['caption'])) {
                    if (!$item['data']['caption']) {
                        throw new \Exception('Заголовок не может быть пустым', 239);
                    }
                    $saveData['caption'] = $item['data']['caption'];
                }
                if (isset($item['data']['seoUrl']) && $item['data']['seoUrl']) {
                    $saveData['seoUrlTmp'] = $item['data']['seoUrl'];
                }
                if (!isset($saveData['seoUrlTmp'])) {
                    $caption = $objItemOrm->get('caption', 'id=' . $id);
                    $saveData['seoUrlTmp'] = word::wordToUrl($caption);
                } // if
                // if isset seoUrl
                if (isset($item['data']['isPublic'])) {
                    $isPublic = (int)$item['data']['isPublic'];
                    $saveData['isPublic'] = $isPublic ? 'yes' : 'no';
                }
                $saveData['treeId'] = $pContId;
                $newId = $objItemOrm->save(['id' => $id], $saveData);

                eventCore::callOffline(
                    event::NAME,
                    event::ACTION_TABLE_SAVE,
                    ['contId' => $pContId],
                    $newId ? : $id
                );

                $return[$id] = $newId ? : $id;
                ;
            }
            // foreach
        }
        return $return;

        // func. saveTableItemData
    }

    public static function getList(integer $pContId) {
        $objItemOrm = new objItemOrm();
        // {select},{edit},Заголовок,СЕО URL, Публиковать
        $list = $objItemOrm->selectAll('id, 0, "", caption, if(trim(seoUrlTmp)="",seoUrl, seoUrlTmp) as seoUrl,'
                                           . 'if(isPublic="yes", 1, 0) isPublic',
                                       ['treeId' => $pContId, 'isDel' => 0], 'date_add desc, id desc');
        return ['body' => $list];
        // func. getList
    }

    // class model
}