<?
// Conf
    use \SITE as CONF;
    use \DB as DB;
    use \DIR as DIR;

    // Const
    use admin\library\classes\constant;

    // Core
    use core\classes\console\request;
    use core\classes\DB\DB as DBCore;
    use core\classes\webserver\nginx;

    // ORM
    use ORM\tree\routeTree;

    // ������ ������� �������
    define('DIR_CONF', './../engine/admin/');

    include(DIR_CONF . 'conf/DIR.php');
    include(DIR_CONF . 'conf/SITE.php');
    include(DIR_CONF . 'conf/CONSTANT.php');

    include DIR::CORE . 'admin/library/function/autoload.php';
    // ������� ��� �������� ��������� ���� ������ � ���������� �������. � PHP 5.4 ��������
    include DIR::CORE . 'core/function/errorHandler.php';
    // ��������� �������� ��
    include DIR::CORE . 'core/classes/DB/adapter/' . CONF::DB_ADAPTER . '/adapter.php';
    umask(0002);

    request::init($argv);

    define('SITE_CORE', './../../SiteCoreFlame/');

    $siteName = request::get('siteName');

    $isDirNotExist = !$siteName || !is_dir(SITE_CORE . $siteName);
    if ($isDirNotExist) {
        // TODO: ������� ��� �� ��������� �������� ������ � ��������
        print 'Error: siteName'.htmlspecialchars($siteName).' not exists or Dir(' . htmlspecialchars(SITE_CORE . $siteName) . ') not found';
        exit;
    }

    include DIR::SITE_CORE . $siteName . '/conf/SITE.php';
    include DIR::SITE_CORE . $siteName . '/conf/DIR.php';
    include DIR::SITE_CORE . $siteName . '/conf/DB.php';

    DBCore::addParam('site', \site\conf\DB::$conf);