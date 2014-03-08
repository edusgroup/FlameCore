<?php

namespace core\classes;

// Библиотека работы с файлами директорий
class filesystem {

    public static function andEndSlash(string $path) {
        return substr($path, strlen($path) - 1, 1) != '/' ? $path.'/' : $path;
    }

    // TODO: описать переменные
    const ALL = 0;
    const FILE = 1;
    const DIR = 2;
    const ALL_NO_FILTER_FOLDER = 3;
    const ALL_NO_FILTER_FILE = 4;

    /**
     * Получаем содержимое директории в виде нумерованного массива<br/>
     * Папки начинающиеся с "." пропускаються
     * @param string $pDir путь к директории
     * @param integer $pFileType тип объекта<br/>
     * <b>filesystem::FILE</b> - выберать только файлы<br/>
     * <b>filesystem:DIR</b> - выберать только директории<br/>
     * <b>filesystem:ALL</b> - все опиции
     * @param string $pFilter Regexp фильтр на имя объекта.<br/>
     * Пример <b>/\.php$/i</b>
     * @return array нумерованный массив
     *
     */
    public static function dir2array(string $pDir, $pFileType = self::ALL, $pFilter = null) {
        if (!is_dir($pDir))
            return [];
        $return = [];
        foreach (scandir($pDir) as $item) {
            if ($item == '.' || $item == '..' || $item[0] == '.'){
                continue;
            }
            $isDir = is_dir($pDir . $item);
            if ($pFileType == self::DIR && !$isDir){
                continue;
            }
            if ($pFileType == self::FILE && $isDir){
                continue;
            }

            $isAdd = true;
            $pFilter = $pFileType == self::ALL_NO_FILTER_FOLDER && $isDir ? null : $pFilter;
            $pFilter = $pFileType == self::ALL_NO_FILTER_FILE && !$isDir ? null : $pFilter;
            if ($pFilter) {
                if ($pFilter[0] != '/') {
                    $isAdd = $pFilter == $item;
                } else {
                    $isAdd = preg_match($pFilter, $item);
                }
            }
            if ($isAdd) {
                $return[] = $item;
            }
        }
        return $return;
        // func. dir2array
    }


    /**
     * Получение списка объектов рекурсивно
     * @static
     * @param string $pDir  путь к директории
     * @param string $pFilter  Regexp фильтр на имя объекта.<br/>
     * Пример <b>/\.php$/i</b>
     * @return array
     */
    public static function rDir2Arr(string $pDir, $pFilter = null) {
        $list = [];
        foreach (scandir($pDir) as $item) {
            // Выкидываем системные папки
            if ($item == '.' || $item == '..'){
                continue;
            }
            // Имя оъекта в папке
            $item = $pDir . "/" . $item;
            if ( is_dir($item)){
                $tmpList = self::rDir2Arr($item, $pFilter);
                $list = array_merge($list, $tmpList);
				$list[] = $item;
            }else{
                $isAdd = true;
                // Есть ли фильтр
                if ($pFilter) {
                    // Regexp ли это
                    if ($pFilter[0] != '/') {
                        $isAdd = $pFilter == $item;
                    } else {
                        $isAdd = preg_match($pFilter, $item);
                    }
                } // if ($pFilter)
                // Нужно ли добавлять
                if ( $isAdd ){
                    $list[] = $item;
                }
            } // if else is_dir($item)
        } // foreach

        return $list;
        // func. rDir2Arr
    }
    
    public static function nsToPath($pNs) {
        return str_replace('\\', '/', $pNs);
        // func. nsToPath
    }

    public static function formatBytes($pSize, $pPrecision=2) {
        if ($pSize < 1024){
            return $pSize . ' B';
        }elseif ($pSize < 1048576){
            return round($pSize / 1024, $pPrecision) . ' KB';
        }elseif ($pSize < 1073741824){
            return round($pSize / 1048576, $pPrecision) . ' MB';
        }elseif ($pSize < 1099511627776){
            return round($pSize / 1073741824, $pPrecision) . ' GB';
        }else{
            return round($pSize / 1099511627776, $pPrecision) . ' TB';
        }
        // func. formatBytes
    }

    /**
     * Получаем первый попавшийся объект из папки
     * @param string $p_dir директория просмотра
     * @param integer $p_file_type тип объекта<br/>
     * filesystem::FILE-выберать только файлы<br/> 
     * filesystem:DIR-выберать только директории<br/>
     * filesystem:ALL - все опиции
     * @param string $p_regex Регекс.Фильтр объектов
     * @return string имя объекта из папки
     */
    public static function getFirstObjectInFolder(string $p_dir, $p_file_type = self::ALL, $p_regex = '') {
        if (!is_dir($p_dir)){
            return;
        }
        foreach (scandir($p_dir) as $item) {
            if ($item == '.' || $item == '..' || $item[0] == '.'){
                continue;
            }
            if ($p_file_type == self::DIR && is_file($p_dir . $item)){
                continue;
            }
            if ($p_file_type == self::FILE && is_dir($p_dir . $item)){
                continue;
            }

            if (!$p_regex || preg_match($p_regex, $item)) {
                return $item;
            }
        }// foreach
        // func. getFirstObjectInFolder
    }

    /**
     * Получаем расширение файла<br />
     * @param string $pFilename имя файла
     * @return string
     * echo getExt('data.<b>jpg</b>');<br />
     * Результат: jpg
     */
    public static function getExt(string $pFilename) {
        //$list = explode('.', $pFilename);
        //return end($list);
        return substr(strrchr($pFilename, '.'), 1);
    }

    public static function getName(string $pFilename){
        $list = explode('.', $pFilename);
        $file = array_shift($list);
        return $file;
    }

    public static function copy($pFileSource, $pPathDist, $pFileName) {
        if (!is_readable($pFileSource)) {
            throw new exception\filesystem('Файла не существует ' . $pFileSource);
        }
        self::mkdir($pPathDist);
        $pathDist = self::andEndSlash($pPathDist);
        return copy($pFileSource, $pathDist . $pFileName);
    }

    public static function copyR($pPathSource, $pPathDist, $pBackGround=false) {
        self::mkdir($pPathDist);
        $bg = $pBackGround ? ' &' : '';
        exec('cp -r '.$pPathSource.' '.$pPathDist. ' '.$bg );
        // func. copyR
    }

    public static function getMimeType(string $pFileName) {
        $finfo = new \finfo(FILEINFO_MIME);
        if (!$finfo) {
            throw \Exception('Opening fileinfo database failed', 34);
        }

        $list = explode(';', $finfo->file($pFileName));
        $charset = substr($list[1], 9);
        return ['type' => $list[0], 'charset' => $charset];
    }

    public static function rmdirR($pPathSource, $pBackGround=false){
        $bg = $pBackGround ? ' &' : '';
        exec('rm -rf '.$pPathSource.' '.$bg );
        // func. rmdirR
    }

    /**
     * Удаление дерева папок
     * @param String $dir Имя папки для удаления
     * @return true в случае успешного удаления
     */
    public static function rmdir(string $pDir) {
        $dir = $pDir;
        if ( substr($pDir, -1) == '/' ){
            $dir = substr($pDir,0, strlen($pDir)-1);
        }
        // Если её нет, то всё хорошо
        if (!file_exists($dir))
            return true;
        // Если это не папка или ссылка
        if (!is_dir($dir) || is_link($dir)) {
            // Удаляем
            if (!unlink($dir)) {
                throw new exception\filesystem('Не удалось удалить файл: ' . $dir);
            }
            return true;
        } // if

        // Значит это директория, сканируем её
        foreach (scandir($dir) as $item) {
            // Выкидываем системные папки
            if ($item == '.' || $item == '..'){
                continue;
            }
            // Имя оъекта в папке
            $file = $dir . "/" . $item;
            // Удаляем рекурсивно в папке объекты
            if (!self::rmdir($file)) {
                // Удалить не получилось, поменяем права что бы можно было удалить
                chmod($file, 0777);
                // Рекурсивно удаляем
                if (!self::rmdir($file)) {
                    throw new exception\filesystem('Не удалось удалить папку: ' . $file);
                } // if
            } // if
        } // foreach
        // Удаляем папку
        return rmdir($dir);
    }

    /**
     * Выводи файл в поток вывода. Медленная функция использует is_readable.<br/>
     * Не рекомендуется для public
     * @static
     * @param $pFilename
     * @return mixed
     */
    public static function printFile($pFilename) {
        if (!is_readable($pFilename)){
            return;
        }
        $fr = fopen($pFilename, 'r');
        if (!$fr){
            return;
        }
        fpassthru($fr);
        fclose($fr);
    }

    /**
     * Удаление все файлов подходящих под Regexp
     * @param string $pRegex regexp
     * @param string $pDir Папка для поиска и удаления
     */
    /* public static function pregUnlink(string $pRegex, string $pDir) {
      $array = getDirArray($pDir, self::FILE, $pRegex);
      $i_count = count($array);
      for ($i = 0; $i < $i_count; $i++) {
      unlink($pDir . $array[$i]);
      }
      } */

    /**
     * Рекусивное удаление файлов
     * @param \string $pPath
     * @param \int $pFileType
     * @param \string $pFilter
     */
    public static function rUnlink($pPath, $pFileType = self::ALL, $pFilter=null) {
        // TODO: Задумать над rm -rf *file*
        $fileList = self::dir2array($pPath, $pFileType, $pFilter);
        $fileCount = count($fileList);
        for ($i = 0; $i < $fileCount; $i++) {
            $filename = $pPath . $fileList[$i];
            if (is_file($filename)) {
                unlink($filename);
                continue;
            }
            self::rUnlink($filename . '/', $pFileType, $pFilter);
        }
    }

    /**
     * @static
     * @param \string $pFile полное имя файла
     * @return bool
     * Удаляем файл с файловой системы. Возвращает TRUE в случае успеха, FALSE в ином
     */
    public static function unlink($pFile) {
        return is_readable($pFile) ? unlink($pFile) : false;
    }

    public static function dir2tree(string $pDir) {
        // Если папки нет, сообщаем об этом
        if (!is_dir($pDir)) {
            //throw new exception\filesystem('Dir not found: ' . $pDir);
            return null;
        }
        $return = [];
        $pos = 0;
        self::_rdir2tree($pDir, $return, $pos);
        return $return;
    }

    /**
     *  Имя объекта в массиве директорий. см. метод self::dir2tree(string, array, integer)
     */
    const ITEM_NAME = 'name';
    /**
     * ID папки в массиве директорий. см. метод self::dir2tree(string, array, integer)
     */
    const ITEM_NUM = 'id';

    private static function _rdir2tree(string $pDir, array &$pDirList, integer &$pParentId) {
        // Сканируем директорию
        $parent = $pParentId;
        foreach (scandir($pDir) as $itemName) {
            // Выкидываем системные папки и скрытые
            if ($itemName == '.' || $itemName == '..' || $itemName[0] == '.'){
                continue;
            }
            // Имя оъекта в папке
            $file = $pDir . "/" . $itemName;

            // Если это файл
            if (is_file($file)) {
                // Добавляем только имя объекта
                $pDirList[$pParentId][] = [self::ITEM_NAME => $itemName];
                // echo $file." P=$pParentId\n";
            } else{
                // Если это папка, то увеличиваем значение родителя на единицу, тем самым получая новый ID
                ++$parent;
                $folderId = $parent;
                // echo $file." P=$pParentId id=$folderId\n";
                self::_rdir2tree($file, $pDirList, $parent);
                $pDirList[$pParentId][] = [self::ITEM_NAME => $itemName, self::ITEM_NUM => $folderId];
            }
        }
        $pParentId = $parent;
        // func. _rdir2tree
    }

    /**
     * Создает рекурсивно папки. Если папка уже существует или была удачно создана.
     * Возвращает <i>true</i>, в ином случае <i>false</i>
     * @param string $pDir Путь каталога
     * @param integer $pRules права доступа на папку. Только для Unix system
     * @return
     * @throws exception\filesystem
     */
    public static function mkdir(string $pDir, $pRules = 0777) {
        if (is_dir($pDir)){
            return true;
        }
        if (mkdir($pDir, $pRules, true)){
            return true;
        }
        throw new exception\filesystem('Не возможно создать папку: ' . $pDir, 24);
        // func. mkdir
    }

    /**
     * @static
     * @param string $pDirSave директория сохранения.
     * @param string $pFile имя файла
     * @param string $pData данные
     * Сохранение данных в файл. Если директории не существует, то она будет создана
     */
    public static function saveFile($pDirSave, $pFile, $pData) {
        self::mkdir($pDirSave);
        $filename = self::andEndSlash($pDirSave) . $pFile;
        return file_put_contents($filename, $pData);
        // func. saveFile
    }

    public static function getFile1of2($p_path_1, $p_path_2, $p_filename) {
        $filename = $p_path_1 . $p_filename;
        if (!file_exists($filename)) {
            $filename = $p_path_2 . $p_filename;
            if (!file_exists($filename)) {
                return '';
            }
        }
        return $filename;
    }

    /**
     * Получает содержимое файла. Медленная функция использует is_readable.<br/>
     * Не рекомендуется для public
     * @static
     * @param $pFilename
     */
    public static function loadFileContent($pFilename){
        return is_readable($pFilename) ? file_get_contents($pFilename) : '';
        // func. loadFileContent
    }

    /**
     * Получает содержимое файла. Медленная функция использует is_readable.<br/>
     * Не рекомендуется для public
     * @static
     * @param $pFilename
     */
    public static function loadFileContentUnSerialize($pFilename){
        if ( !is_readable($pFilename) ){
           return null;
        }
        $data = file_get_contents($pFilename);
        return \unserialize($data);
        // func. loadFileContent
    }

    public static function rename($pOldFile, $pNewFile){
        if (is_file($pOldFile)){
            \rename($pOldFile, $pNewFile);
        }
        // func. rename
    }
// class. filesystem
}
