<?php

namespace core\comp\spl\oiComment\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\word;
use core\classes\userUtils;
use core\classes\site\dir as sitePath;
use core\classes\filesystem;

// ORM
use ORM\comp\spl\oiComment\seoComment as seoCommentOrm;

/**
 * Description of main
 *
 * @author �������� �.�.
 */
class seo {
	
	/**
	* $className - �����������. Handle �� ����������� �����, ������� �������� ��� �������
	*/
    public static function renderAction($pName, $className=null) {
		$comp = dbus::$comp[$pName];
		$dir = DIR::APP_DATA.'comp/'.$comp['compId'].'/'.$comp['contId'].'/';
		
		$ua = $_SERVER['HTTP_USER_AGENT'];
		
  
		//if ( preg_match('/Google|Yandex/', $_SERVER['HTTP_USER_AGENT']) ){
		if ( preg_match('/YandexBot/', $ua) || isset($_GET['addcomm']) || false){
			
		
			$seoCommentOrm = new seoCommentOrm();
			$allData = $seoCommentOrm->selectAll('*', ['isPublish'=>1]);
			
			$adddate = $seoCommentOrm->sql('SELECT  DATE_ADD(MAX(dateadd), INTERVAL  round(RAND()* TIME_TO_SEC( TIMEDIFF(NOW(), MAX(dateadd)) ) ) SECOND ) as newdate from pr_seo_comments where isPublish = 1')->fetchFirst(); 
			$adddate = $adddate['newdate'];
		
			$newComment = $seoCommentOrm->select('*')->where(['isPublish'=>0])->order('')->limit(1)->fetchAll();
			if ( isset($newComment[0]) ){
				//$date = date('Y-m-d H:i:s');
				$newComment[0]['dateadd'] = $adddate;
				$allData[] = $newComment[0];
				$seoCommentOrm->update(['isPublish'=>1, 'dateadd'=>$adddate], ['id'=>$newComment[0]['id']]);
			}

			filesystem::mkdir($dir);
			
			$tplFile = userUtils::getCompTpl($comp);
			$tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']); 
			
			$commFile = preg_replace('/([^\/]+)$/', '_comment-$1', $tplFile );

			$render = new render($tplPath, '');
			$render->setMainTpl($tplFile)
					->setContentType(null)
					->setVar('allData', $allData)
					->setBlock('commFile', $commFile)
					->renderToFile($dir.'data.html', ''); 
					//->render();  
		} // if ( preg_match('/Google|Yandex/', $_SERVER['HTTP_USER_AGENT']) )

		
		$fr = @fopen($dir.'data.html', 'r');
        if (!$fr){
            return;
        }
        fpassthru($fr);
        fclose($fr);
		// func. renderAction
    }

}