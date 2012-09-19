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
        $return = ['seoUrl' => [], 'listId' => []];
        // Парсим данны
        $data = json_decode($pData, true);
        if ($data) {
            $objItemOrm = new objItemOrm();

            // Бегаем по данным
            foreach ($data as $item) {
                // Если ID не указан, что то тут не то, говорим что ошибка
                if (!isset($item['id'])) {
                    throw new \Exception('Неверный JSON', 234);
                } // if

                $id = (int)$item['id'];
                $saveData = [];

                // Получам заголовок записи
                $caption = isset($item['data']['caption']) ? $item['data']['caption'] : null;
                if ( $caption !== null ){
                    if ( !$caption ){
                        throw new \Exception('Пустой заголовок', 239);
                    }
                    $saveData['caption'] = $caption;
                } // if

                // Получаем URL заголовок для записи
                $seoUrl = isset($item['data']['seoUrl']) ? $item['data']['seoUrl'] : null;
                // Если запрос пустой, те. он не изменился или пришёл пустым
                if ( !$seoUrl ) {
                    // Получаем что раньше сохраняли
                    $data = $objItemOrm->selectFirst('seoUrl, caption, seoUrlTmp', 'id=' . $id);
                    // Если сохранений нет, или сео параметры пусты, или сео параметры пришли, но они пустые
                    if (!$data || !$data['seoUrl'] && !$data['seoUrlTmp'] || $seoUrl !== null && !$seoUrl){
                        // Получаем заголовок, тот который пришел или вытаскиваем ранее сохранёный
                        $seoUrlTmp = $caption ?: $data['caption'];
                        $saveData['seoUrlTmp'] = word::wordToUrl($seoUrlTmp);
                    } // if !$seoUrl
                }else{
                    $saveData['seoUrlTmp'] = $seoUrl;
                } // if

                if (isset($item['data']['isPublic'])) {
                    $isPublic = (int)$item['data']['isPublic'];
                    $saveData['isPublic'] = $isPublic ? 'yes' : 'no';
                } // if

                $saveData['treeId'] = $pContId;
                $newId = $objItemOrm->save(['id' => $id], $saveData);

                eventCore::callOffline(
                    event::NAME,
                    event::ACTION_TABLE_SAVE,
                    ['contId' => $pContId],
                    $newId ? : $id
                );

                $return['listId'][$id] = $newId ? : $id;
                if ( isset($saveData['seoUrlTmp'])){
                    $return['seoUrl'][$id] = $saveData['seoUrlTmp'];
                }
            } // foreach

        } // if $data
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