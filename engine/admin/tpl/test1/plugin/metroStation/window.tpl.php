<!DOCTYPE html>
<html>
<head>
    <title>Выберите карты</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script src="http://www.google.com/jsapi" type="text/javascript"></script>
    <script type="text/javascript">
        google.load("jquery", "1");
        google.load("jqueryui", "1");
    </script>
    <!--<script src="http://code.jquery.com/jquery-latest.js"></script>-->

    <script type="text/javascript" src="http://theme.codecampus.ru/plugin/metroStation/js/stations.js"></script>
    <script type="text/javascript" src="http://theme.codecampus.ru/plugin/metroStation/js/map.js"></script>

    <link type="text/css" rel="stylesheet" href="http://theme.codecampus.ru/plugin/metroStation/css/main.css"/>
    <link type="text/css" rel="stylesheet" href="http://theme.codecampus.ru/plugin/metroStation/css/common.css"/>
    <link type="text/css" rel="stylesheet" href="http://theme.codecampus.ru/plugin/metroStation/css/moscow.css"/>
</head>
<body bgcolor="#ffffff">

<div id="panel-canvas">
    <div id="panel">
        <div id="panel-content">
            <div id="findIcon" title="Поиск станции"><br></div>

            <div id="findDrop" class="stations-button">
                <div class="bgr"><br></div>
                <em class="empty"><br></em>
                <dl><dt>Поиск станции...</dt><dd>Поиск станции...</dd></dl>
            </div>


            <div id="stations">
                <div class="search">
                    <input type="text">
                    <div class="clear"><br></div>
                </div>
                <div class="list">
                    <div class="up"><br></div>
                    <div class="scroll">
                        <p class="empty">Таких станций нет</p>
                        <ul class="items">
                            <li>Авиамоторная</li><li>Автозаводская</li><li>Академическая</li><li>Александровский сад</li><li>Алексеевская</li><li>Алтуфьево</li><li>Аннино</li><li>Арбатская</li><li>Арбатская</li><li>Аэропорт</li><li>Бабушкинская</li><li>Багратионовская</li><li>Баррикадная</li><li>Бауманская</li><li>Беговая</li><li>Белорусская</li><li>Белорусская</li><li>Беляево</li><li>Бибирево</li><li>Библиотека им. Ленина</li><li>Борисово</li><li>Боровицкая</li><li>Ботанический сад</li><li>Братиславская</li><li>Бульвар адмирала Ушакова</li><li>Бульвар Дмитрия Донского</li><li>Бунинская аллея</li><li>Варшавская</li><li>ВДНХ</li><li>Владыкино</li><li>Водный стадион</li><li>Войковская</li><li>Волгоградский проспект</li><li>Волжская</li><li>Волоколамская</li><li>Воробьевы горы</li><li>Выставочная</li><li>Выхино</li><li>Динамо</li><li>Дмитровская</li><li>Добрынинская</li><li>Домодедовская</li><li>Достоевская</li><li>Дубровка</li><li>Зябликово</li><li>Измайловская</li><li>Калужская</li><li>Кантемировская</li><li>Каховская</li><li>Каширская</li><li>Каширская</li><li>Киевская</li><li>Киевская</li><li>Киевская</li><li>Китай-город</li><li>Китай-город</li><li>Кожуховская</li><li>Коломенская</li><li>Комсомольская</li><li>Комсомольская</li><li>Коньково</li><li>Красногвардейская</li><li>Краснопресненская</li><li>Красносельская</li><li>Красные ворота</li><li>Крестьянская застава</li><li>Кропоткинская</li><li>Крылатское</li><li>Кузнецкий мост</li><li>Кузьминки</li><li>Кунцевская</li><li>Кунцевская</li><li>Курская</li><li>Курская</li><li>Кутузовская</li><li>Ленинский проспект</li><li>Лубянка</li><li>Люблино</li><li>Марксистская</li><li>Марьина роща</li><li>Марьино</li><li>Маяковская</li><li>Медведково</li><li>Международная</li><li>Менделеевская</li><li>Митино</li><li>Молодежная</li><li>Мякинино</li><li>Нагатинская</li><li>Нагорная</li><li>Нахимовский проспект</li><li>Новогиреево</li><li>Новокосино</li><li>Новокузнецкая</li><li>Новослободская</li><li>Новоясеневская</li><li>Новые черемушки</li><li>Октябрьская</li><li>Октябрьская</li><li>Октябрьское поле</li><li>Орехово</li><li>Отрадное</li><li>Охотный ряд</li><li>Павелецкая</li><li>Павелецкая</li><li>Парк культуры</li><li>Парк культуры</li><li>Парк Победы</li><li>Партизанская</li><li>Первомайская</li><li>Перово</li><li>Петровско-Разумовская</li><li>Печатники</li><li>Пионерская</li><li>Планерная</li><li>Площадь Ильича</li><li>Площадь революции</li><li>Полежаевская</li><li>Полянка</li><li>Пражская</li><li>Преображенская площадь</li><li>Пролетарская</li><li>Проспект Вернадского</li><li>Проспект мира</li><li>Проспект мира</li><li>Профсоюзная</li><li>Пушкинская</li><li>Речной вокзал</li><li>Рижская</li><li>Римская</li><li>Рязанский проспект</li><li>Савёловская</li><li>Свиблово</li><li>Севастопольская</li><li>Семеновская</li><li>Серпуховская</li><li>Славянский бульвар</li><li>Смоленская</li><li>Смоленская</li><li>Сокол</li><li>Сокольники</li><li>Спортивная</li><li>Сретенский бульвар</li><li>Строгино</li><li>Студенческая</li><li>Сухаревская</li><li>Сходненская</li><li>Таганская</li><li>Таганская</li><li>Тверская</li><li>Театральная</li><li>Текстильщики</li><li>Теплый стан</li><li>Тимирязевская</li><li>Третьяковская</li><li>Третьяковская</li><li>Трубная</li><li>Тульская</li><li>Тургеневская</li><li>Тушинская</li><li>Улица 1905 года</li><li>Улица академика Янгеля</li><li>Улица Горчакова</li><li>Улица Подбельского</li><li>Улица Скобелевская</li><li>Улица Старокачаловская</li><li>Университет</li><li>Филевский парк</li><li>Фили</li><li>Фрунзенская</li><li>Царицыно</li><li>Цветной бульвар</li><li>Черкизовская</li><li>Чертановская</li><li>Чеховская</li><li>Чистые пруды</li><li>Чкаловская</li><li>Шаболовская</li><li>Шипиловская</li><li>Шоссе энтузиастов</li><li>Щелковская</li><li>Щукинская</li><li>Электрозаводская</li><li>Юго-Западная</li><li>Южная</li><li>Ясенево</li>
                        </ul>
                        <ul class="reset">
                            <li>Отменить выбор</li>
                        </ul>
                    </div>
                    <div class="down"><br></div>
                </div>
            </div>

            <div id="clearBtn" title="Очистить выбранные станции"><a class="action">Очистить</a></div>
            <div id="selectBtn" title="Выбрать выделенные станции"></div>

        </div>
    </div>
</div>
<script type="text/javascript">
    stationsList.init(["kal_84", "zam_21", "kar_45", "fil_149", "kar_105", "set_117", "set_23", "arp_152", "fil_151", "zam_123", "kar_96", "fil_153", "tkr_137", "arp_88", "tkr_134", "zam_7", "kol_7", "kar_48", "set_119", "sok_157", "lub_-", "set_158", "kar_103", "lub_16", "but_-", "set_22", "but_-", "kah_17", "kar_104", "set_118", "zam_127", "zam_125", "tkr_80", "lub_74", "arp_167", "sok_40", "fil_60", "tkr_69", "zam_122", "set_115", "kol_34", "zam_12", "lub_108", "lub_77", "lub_-", "arp_92", "kar_47", "zam_14", "kah_18", "zam_2", "kah_2", "arp_9", "fil_9", "kol_9", "kar_8", "tkr_8", "lub_76", "zam_19", "sok_5", "kol_5", "kar_49", "zam_11", "kol_61", "sok_98", "sok_106", "lub_78", "sok_55", "arp_164", "tkr_145", "tkr_71", "arp_10", "fil_10", "arp_58", "kol_58", "fil_155", "kar_46", "sok_144", "lub_73", "kal_56", "lub_114", "lub_72", "zam_136", "kar_97", "fil_62", "set_113", "arp_168", "arp_163", "arp_166", "set_31", "set_30", "set_29", "kal_87", "kal_-", "zam_35", "kol_112", "kar_52", "kar_43", "kol_4", "kar_4", "tkr_132", "zam_13", "set_120", "sok_148", "zam_3", "kol_3", "sok_37", "kol_37", "arp_154", "arp_91", "arp_93", "kal_86", "set_121", "lub_75", "fil_162", "tkr_129", "kal_82", "arp_146", "tkr_133", "set_54", "set_25", "sok_100", "tkr_81", "sok_38", "kol_6", "kar_6", "kar_44", "tkr_138", "zam_126", "kar_107", "lub_83", "tkr_70", "set_116", "kar_95", "set_28", "arp_90", "set_33", "arp_169", "arp_150", "fil_159", "zam_124", "sok_99", "sok_41", "lub_141", "arp_165", "fil_160", "kar_109", "tkr_128", "kol_57", "tkr_57", "zam_139", "zam_147", "tkr_79", "kar_50", "set_67", "kar_36", "kal_36", "lub_110", "set_32", "kar_143", "tkr_130", "tkr_135", "set_24", "but_-", "sok_102", "but_-", "but_-", "sok_39", "fil_161", "fil_156", "sok_42", "zam_15", "set_111", "sok_101", "set_27", "set_140", "sok_142", "lub_59", "kar_1", "lub_-", "kal_85", "arp_94", "tkr_131", "arp_89", "sok_53", "set_26", "kar_51"]);
</script>

<div id="markerBox">
    <img name="map" src="http://theme.codecampus.ru/plugin/metroStation/img/moscow.png" width="809" height="863" id="map" usemap="#stationCoor" alt=""/>
</div>
<map id="stationCoor">
<area shape="rect" coords="348,610,368,625" href="#1" title="Шаболовская" alt="Шаболовская"/>
<area shape="rect" coords="281,606,350,621" href="#1" title="Шаболовская" alt="Шаболовская"/>
<area shape="rect" coords="530,709,591,721" href="#2" title="Каширская" alt="Каширская"/>
<area shape="rect" coords="487,707,533,731" href="#2" title="Каширская" alt="Каширская"/>
<area shape="rect" coords="460,558,544,605" href="#3" title="Павелецкая" alt="Павелецкая"/>
<area shape="rect" coords="298,547,391,584" href="#4" title="Октябрьская" alt="Октябрьская"/>
<area shape="rect" coords="519,261,654,282" href="#5" title="Комсомольская" alt="Комсомольская"/>
<area shape="rect" coords="483,215,560,255" href="#6" title="Проспект мира" alt="Проспект мира"/>
<area shape="rect" coords="297,261,362,276" href="#7" title="Белорусская" alt="Белорусская"/>
<area shape="rect" coords="259,245,297,282" href="#7" title="Белорусская" alt="Белорусская"/>
<area shape="rect" coords="475,427,545,463" href="#8" title="Китай-город" alt="Китай-город"/>
<area shape="rect" coords="260,471,308,485" href="#9" title="Киевская" alt="Киевская"/>
<area shape="rect" coords="215,449,263,506" href="#9" title="Киевская" alt="Киевская"/>
<area shape="rect" coords="33,390,75,407" href="#10" title="Кунцевская" alt="Кунцевская"/>
<area shape="rect" coords="23,373,89,387" href="#10" title="Кунцевская" alt="Кунцевская"/>
<area shape="rect" coords="556,803,573,825" href="#11" title="Красногвардейская" alt="Красногвардейская"/>
<area shape="rect" coords="513,814,619,837" href="#11" title="Красногвардейская" alt="Красногвардейская"/>
<area shape="rect" coords="456,788,558,804" href="#12" title="Домодедовская" alt="Домодедовская"/>
<area shape="rect" coords="525,768,591,788" href="#13" title="Орехово" alt="Орехово"/>
<area shape="rect" coords="521,739,615,754" href="#14" title="Кантемировская" alt="Кантемировская"/>
<area shape="rect" coords="509,735,528,750" href="#14" title="Кантемировская" alt="Кантемировская"/>
<area shape="rect" coords="453,754,525,773" href="#15" title="Царицыно" alt="Царицыно"/>
<area shape="rect" coords="607,715,645,737" href="#16" title="Братиславская" alt="Братиславская"/>
<area shape="rect" coords="541,721,616,737" href="#16" title="Братиславская" alt="Братиславская"/>
<area shape="rect" coords="451,702,483,729" href="#17" title="Варшавская" alt="Варшавская"/>
<area shape="rect" coords="439,696,503,707" href="#17" title="Варшавская" alt="Варшавская"/>
<area shape="rect" coords="425,729,479,745" href="#18" title="Каховская" alt="Каховская"/>
<area shape="rect" coords="426,707,447,735" href="#18" title="Каховская" alt="Каховская"/>
<area shape="rect" coords="505,684,528,702" href="#19" title="Коломенская" alt="Коломенская"/>
<area shape="rect" coords="442,678,517,693" href="#19" title="Коломенская" alt="Коломенская"/>
<area shape="rect" coords="453,655,533,678" href="#20" title="Технопарк" alt="Технопарк"/>
<area shape="rect" coords="456,623,535,650" href="#21" title="Автозаводская" alt="Автозаводская"/>
<area shape="rect" coords="387,845,550,863" href="#22" title="Бульвар Дмитрия Донского" alt="Бульвар Дмитрия Донского"/>
<area shape="rect" coords="424,810,468,840" href="#23" title="Аннино" alt="Аннино"/>
<area shape="rect" coords="424,788,443,810" href="#24" title="Ул. академика Янгеля" alt="Ул. академика Янгеля"/>
<area shape="rect" coords="340,791,421,813" href="#24" title="Ул. академика Янгеля" alt="Ул. академика Янгеля"/>
<area shape="rect" coords="372,770,443,788" href="#25" title="Пражская" alt="Пражская"/>
<area shape="rect" coords="421,750,443,768" href="#26" title="Южная" alt="Южная"/>
<area shape="rect" coords="387,754,431,768" href="#26" title="Южная" alt="Южная"/>
<area shape="rect" coords="403,731,424,750" href="#27" title="Чертановская" alt="Чертановская"/>
<area shape="rect" coords="329,735,406,750" href="#27" title="Чертановская" alt="Чертановская"/>
<area shape="rect" coords="400,710,425,732" href="#28" title="Севастопольская" alt="Севастопольская"/>
<area shape="rect" coords="311,717,403,735" href="#28" title="Севастопольская" alt="Севастопольская"/>
<area shape="rect" coords="335,688,424,709" href="#29" title="Нахимовский проспект" alt="Нахимовский проспект"/>
<area shape="rect" coords="350,668,424,688" href="#30" title="Нагорная" alt="Нагорная"/>
<area shape="rect" coords="362,645,449,667" href="#31" title="Нагатинская" alt="Нагатинская"/>
<area shape="rect" coords="381,623,451,642" href="#32" title="Тульская" alt="Тульская"/>
<area shape="rect" coords="426,591,453,614" href="#33" title="Серпуховская" alt="Серпуховская"/>
<area shape="rect" coords="354,591,428,606" href="#33" title="Серпуховская" alt="Серпуховская"/>
<area shape="rect" coords="400,558,453,587" href="#34" title="Добрынинская" alt="Добрынинская"/>
<area shape="rect" coords="401,535,468,553" href="#35" title="Новокузнецкая" alt="Новокузнецкая"/>
<area shape="rect" coords="412,519,437,542" href="#35" title="Новокузнецкая" alt="Новокузнецкая"/>
<area shape="rect" coords="437,507,461,532" href="#36" title="Третьяковская" alt="Третьяковская"/>
<area shape="rect" coords="415,494,517,519" href="#36" title="Третьяковская" alt="Третьяковская"/>
<area shape="rect" coords="266,501,308,528" href="#37" title="Парк культуры" alt="Парк культуры"/>
<area shape="rect" coords="266,524,371,538" href="#37" title="Парк культуры" alt="Парк культуры"/>
<area shape="rect" coords="70,652,208,668" href="#38" title="Проспект Вернадского" alt="Проспект Вернадского"/>
<area shape="rect" coords="134,635,222,652" href="#39" title="Университет" alt="Университет"/>
<area shape="rect" coords="129,617,241,635" href="#40" title="Воробьёвы горы" alt="Воробьёвы горы"/>
<area shape="rect" coords="184,587,268,606" href="#41" title="Спортивная" alt="Спортивная"/>
<area shape="rect" coords="191,564,292,582" href="#42" title="Фрунзенская" alt="Фрунзенская"/>
<area shape="rect" coords="284,676,300,693" href="#43" title="Новые черёмушки" alt="Новые черёмушки"/>
<area shape="rect" coords="210,673,284,690" href="#43" title="Новые черёмушки" alt="Новые черёмушки"/>
<area shape="rect" coords="299,657,316,676" href="#44" title="Профсоюзная" alt="Профсоюзная"/>
<area shape="rect" coords="226,655,300,673" href="#44" title="Профсоюзная" alt="Профсоюзная"/>
<area shape="rect" coords="315,642,333,657" href="#45" title="Академическая" alt="Академическая"/>
<area shape="rect" coords="229,640,316,655" href="#45" title="Академическая" alt="Академическая"/>
<area shape="rect" coords="329,623,351,642" href="#46" title="Ленинский проспект" alt="Ленинский проспект"/>
<area shape="rect" coords="258,622,332,640" href="#46" title="Ленинский проспект" alt="Ленинский проспект"/>
<area shape="rect" coords="207,690,284,709" href="#47" title="Калужская" alt="Калужская"/>
<area shape="rect" coords="205,709,270,726" href="#48" title="Беляево" alt="Беляево"/>
<area shape="rect" coords="197,728,270,743" href="#49" title="Коньково" alt="Коньково"/>
<area shape="rect" coords="181,746,270,765" href="#50" title="Тёплый стан" alt="Тёплый стан"/>
<area shape="rect" coords="217,765,284,780" href="#51" title="Ясенево" alt="Ясенево"/>
<area shape="rect" coords="192,780,299,806" href="#52" title="Новоясеневская" alt="Новоясеневская"/>
<area shape="rect" coords="111,705,205,726" href="#53" title="Юго-западная" alt="Юго-западная"/>
<area shape="rect" coords="316,505,381,521" href="#54" title="Полянка" alt="Полянка"/>
<area shape="rect" coords="310,481,406,501" href="#55" title="Кропоткинская" alt="Кропоткинская"/>
<area shape="rect" coords="537,524,608,544" href="#56" title="Марксистская" alt="Марксистская"/>
<area shape="rect" coords="568,508,589,528" href="#56" title="Марксистская" alt="Марксистская"/>
<area shape="rect" coords="546,491,567,519" href="#57" title="Таганская" alt="Таганская"/>
<area shape="rect" coords="564,484,590,505" href="#57" title="Таганская" alt="Таганская"/>
<area shape="rect" coords="510,481,580,494" href="#57" title="Таганская" alt="Таганская"/>
<area shape="rect" coords="544,434,590,450" href="#58" title="Курская" alt="Курская"/>
<area shape="rect" coords="573,413,625,437" href="#58" title="Курская" alt="Курская"/>
<area shape="rect" coords="590,437,683,455" href="#59" title="Чкаловская" alt="Чкаловская"/>
<area shape="rect" coords="166,409,197,430" href="#60" title="Выставочная" alt="Выставочная"/>
<area shape="rect" coords="156,398,222,412" href="#60" title="Выставочная" alt="Выставочная"/>
<area shape="rect" coords="174,369,241,386" href="#61" title="Краснопресненская" alt="Краснопресненская"/>
<area shape="rect" coords="225,349,244,372" href="#61" title="Краснопресненская" alt="Краснопресненская"/>
<area shape="rect" coords="124,365,156,383" href="#62" title="Международная" alt="Международная"/>
<area shape="rect" coords="93,355,188,368" href="#62" title="Международная" alt="Международная"/>
<area shape="rect" coords="584,114,605,134" href="#63" title="Ул. Сергея Эйзенштейна" alt="Ул. Сергея Эйзенштейна"/>
<area shape="rect" coords="541,97,612,116" href="#63" title="Ул. Сергея Эйзенштейна" alt="Ул. Сергея Эйзенштейна"/>
<area shape="rect" coords="537,116,555,137" href="#64" title="Выставочный центр" alt="Выставочный центр"/>
<area shape="rect" coords="508,135,581,153" href="#64" title="Выставочный центр" alt="Выставочный центр"/>
<area shape="rect" coords="494,112,509,133" href="#65" title="Ул. академика Королёва" alt="Ул. академика Королёва"/>
<area shape="rect" coords="475,92,537,116" href="#65" title="Ул. академика Королёва" alt="Ул. академика Королёва"/>
<area shape="rect" coords="442,119,464,138" href="#66" title="Телецентр" alt="Телецентр"/>
<area shape="rect" coords="426,135,487,149" href="#66" title="Телецентр" alt="Телецентр"/>
<area shape="rect" coords="354,117,370,135" href="#67" title="Тимирязевская" alt="Тимирязевская"/>
<area shape="rect" coords="254,123,356,141" href="#67" title="Тимирязевская" alt="Тимирязевская"/>
<area shape="rect" coords="370,102,448,120" href="#68" title="Улица Милашенко" alt="Улица Милашенко"/>
<area shape="rect" coords="400,116,415,135" href="#68" title="Улица Милашенко" alt="Улица Милашенко"/>
<area shape="rect" coords="680,732,754,761" href="#69" title="Выхино" alt="Выхино"/>
<area shape="rect" coords="680,682,779,707" href="#70" title="Рязанский проспект" alt="Рязанский проспект"/>
<area shape="rect" coords="680,649,776,673" href="#71" title="Кузьминки" alt="Кузьминки"/>
<area shape="rect" coords="603,754,654,773" href="#72" title="Марьино" alt="Марьино"/>
<area shape="rect" coords="616,743,639,756" href="#72" title="Марьино" alt="Марьино"/>
<area shape="rect" coords="569,690,641,709" href="#73" title="Люблино" alt="Люблино"/>
<area shape="rect" coords="564,668,642,688" href="#74" title="Волжская" alt="Волжская"/>
<area shape="rect" coords="558,645,642,665" href="#75" title="Печатники" alt="Печатники"/>
<area shape="rect" coords="546,621,640,640" href="#76" title="Кожуховская" alt="Кожуховская"/>
<area shape="rect" coords="562,595,642,617" href="#77" title="Дубровка" alt="Дубровка"/>
<area shape="rect" coords="560,558,642,587" href="#78" title="Крестьянская застава" alt="Крестьянская застава"/>
<area shape="rect" coords="678,588,773,621" href="#79" title="Текстильщики" alt="Текстильщики"/>
<area shape="rect" coords="650,557,773,582" href="#80" title="Волгоградский проспект" alt="Волгоградский проспект"/>
<area shape="rect" coords="612,532,712,553" href="#81" title="Пролетарская" alt="Пролетарская"/>
<area shape="rect" coords="612,507,731,530" href="#82" title="Площадь Ильича" alt="Площадь Ильича"/>
<area shape="rect" coords="607,470,654,506" href="#83" title="Римская" alt="Римская"/>
<area shape="rect" coords="668,478,769,501" href="#84" title="Авиамоторная" alt="Авиамоторная"/>
<area shape="rect" coords="700,445,792,478" href="#85" title="Шоссе энтузиастов" alt="Шоссе энтузиастов"/>
<area shape="rect" coords="700,426,771,442" href="#86" title="Перово" alt="Перово"/>
<area shape="rect" coords="703,403,792,421" href="#87" title="Новогиреево" alt="Новогиреево"/>
<area shape="rect" coords="626,394,699,421" href="#88" title="Бауманская" alt="Бауманская"/>
<area shape="rect" coords="655,368,776,386" href="#89" title="Электрозаводская" alt="Электрозаводская"/>
<area shape="rect" coords="680,341,769,357" href="#90" title="Семёновская" alt="Семёновская"/>
<area shape="rect" coords="678,316,776,335" href="#91" title="Партизанская" alt="Партизанская"/>
<area shape="rect" coords="680,286,781,307" href="#92" title="Измайловская" alt="Измайловская"/>
<area shape="rect" coords="680,259,781,278" href="#93" title="Первомайская" alt="Первомайская"/>
<area shape="rect" coords="680,230,769,250" href="#94" title="Щелковская" alt="Щелковская"/>
<area shape="rect" coords="612,72,688,92" href="#95" title="Свиблово" alt="Свиблово"/>
<area shape="rect" coords="612,41,709,64" href="#96" title="Бабушкинская" alt="Бабушкинская"/>
<area shape="rect" coords="612,10,699,29" href="#97" title="Медведково" alt="Медведково"/>
<area shape="rect" coords="575,241,674,259" href="#98" title="Красносельская" alt="Красносельская"/>
<area shape="rect" coords="591,219,675,240" href="#99" title="Сокольники" alt="Сокольники"/>
<area shape="rect" coords="615,193,746,212" href="#100" title="Преображенская площадь" alt="Преображенская площадь"/>
<area shape="rect" coords="645,168,739,186" href="#101" title="Черкизовская" alt="Черкизовская"/>
<area shape="rect" coords="643,138,752,153" href="#102" title="ул. Подбельского" alt="ул. Подбельского"/>
<area shape="rect" coords="615,103,709,120" href="#103" title="Ботанический сад" alt="Ботанический сад"/>
<area shape="rect" coords="585,134,631,151" href="#104" title="ВДНХ" alt="ВДНХ"/>
<area shape="rect" coords="553,165,642,181" href="#105" title="Алексеевская" alt="Алексеевская"/>
<area shape="rect" coords="505,285,607,304" href="#106" title="Красные ворота" alt="Красные ворота"/>
<area shape="rect" coords="525,196,587,212" href="#107" title="Рижская" alt="Рижская"/>
<area shape="rect" coords="428,199,508,215" href="#108" title="Достоевская" alt="Достоевская"/>
<area shape="rect" coords="421,255,501,272" href="#109" title="Сухаревская" alt="Сухаревская"/>
<area shape="rect" coords="428,274,493,297" href="#110" title="Трубная" alt="Трубная"/>
<area shape="rect" coords="323,276,426,297" href="#111" title="Цветной бульвар" alt="Цветной бульвар"/>
<area shape="rect" coords="323,220,412,251" href="#112" title="Новослободская" alt="Новослободская"/>
<area shape="rect" coords="333,201,424,220" href="#113" title="Менделеевская" alt="Менделеевская"/>
<area shape="rect" coords="426,180,448,195" href="#114" title="Марьина роща" alt="Марьина роща"/>
<area shape="rect" coords="406,168,490,178" href="#114" title="Марьина роща" alt="Марьина роща"/>
<area shape="rect" coords="338,155,356,171" href="#115" title="Дмитровская" alt="Дмитровская"/>
<area shape="rect" coords="270,149,343,165" href="#115" title="Дмитровская" alt="Дмитровская"/>
<area shape="rect" coords="337,178,421,193" href="#116" title="Савёловская" alt="Савёловская"/>
<area shape="rect" coords="333,3,395,35" href="#117" title="Алтуфьево" alt="Алтуфьево"/>
<area shape="rect" coords="356,72,434,88" href="#118" title="Владыкино" alt="Владыкино"/>
<area shape="rect" coords="354,35,428,52" href="#119" title="Бибирево" alt="Бибирево"/>
<area shape="rect" coords="300,54,379,67" href="#120" title="Отрадное" alt="Отрадное"/>
<area shape="rect" coords="266,86,357,111" href="#121" title="Петровско-разумовская" alt="Петровско-разумовская"/>
<area shape="rect" coords="200,230,258,249" href="#122" title="Динамо" alt="Динамо"/>
<area shape="rect" coords="218,210,291,226" href="#123" title="Аэропорт" alt="Аэропорт"/>
<area shape="rect" coords="200,189,251,207" href="#124" title="Сокол" alt="Сокол"/>
<area shape="rect" coords="200,165,286,181" href="#125" title="Войковская" alt="Войковская"/>
<area shape="rect" coords="166,112,251,138" href="#126" title="Речной вокзал" alt="Речной вокзал"/>
<area shape="rect" coords="117,143,225,162" href="#127" title="Водный стадион" alt="Водный стадион"/>
<area shape="rect" coords="86,197,172,211" href="#128" title="Сходненская" alt="Сходненская"/>
<area shape="rect" coords="93,168,175,185" href="#129" title="Планерная" alt="Планерная"/>
<area shape="rect" coords="92,230,173,245" href="#130" title="Тушинская" alt="Тушинская"/>
<area shape="rect" coords="91,257,177,272" href="#131" title="Щукинская" alt="Щукинская"/>
<area shape="rect" coords="91,276,171,297" href="#132" title="Октябрьское поле" alt="Октябрьское поле"/>
<area shape="rect" coords="166,299,188,316" href="#133" title="Полежаевская" alt="Полежаевская"/>
<area shape="rect" coords="99,311,179,324" href="#133" title="Полежаевская" alt="Полежаевская"/>
<area shape="rect" coords="195,324,211,342" href="#134" title="Беговая" alt="Беговая"/>
<area shape="rect" coords="149,327,195,341" href="#134" title="Беговая" alt="Беговая"/>
<area shape="rect" coords="210,337,226,355" href="#135" title="Ул. 1905 года" alt="Ул. 1905 года"/>
<area shape="rect" coords="222,329,294,342" href="#135" title="Ул. 1905 года" alt="Ул. 1905 года"/>
<area shape="rect" coords="260,311,340,327" href="#136" title="Маяковская" alt="Маяковская"/>
<area shape="rect" coords="244,349,268,372" href="#137" title="Баррикадная" alt="Баррикадная"/>
<area shape="rect" coords="263,365,333,380" href="#137" title="Баррикадная" alt="Баррикадная"/>
<area shape="rect" coords="340,355,359,372" href="#138" title="Пушкинская" alt="Пушкинская"/>
<area shape="rect" coords="277,345,343,357" href="#138" title="Пушкинская" alt="Пушкинская"/>
<area shape="rect" coords="363,353,382,372" href="#139" title="Тверская" alt="Тверская"/>
<area shape="rect" coords="381,345,431,357" href="#139" title="Тверская" alt="Тверская"/>
<area shape="rect" coords="351,332,372,352" href="#140" title="Чеховская" alt="Чеховская"/>
<area shape="rect" coords="347,322,408,335" href="#140" title="Чеховская" alt="Чеховская"/>
<area shape="rect" coords="414,310,479,337" href="#141" title="Сретенский бульвар" alt="Сретенский бульвар"/>
<area shape="rect" coords="483,304,575,322" href="#142" title="Чистые пруды" alt="Чистые пруды"/>
<area shape="rect" coords="483,329,577,345" href="#143" title="Тургеневская" alt="Тургеневская"/>
<area shape="rect" coords="434,354,501,372" href="#144" title="Лубянка" alt="Лубянка"/>
<area shape="rect" coords="451,372,560,389" href="#145" title="Кузнецкий мост" alt="Кузнецкий мост"/>
<area shape="rect" coords="432,420,451,438" href="#146" title="Площадь революции" alt="Площадь революции"/>
<area shape="rect" coords="451,409,555,421" href="#146" title="Площадь революции" alt="Площадь революции"/>
<area shape="rect" coords="418,403,434,421" href="#147" title="Театральная" alt="Театральная"/>
<area shape="rect" coords="434,394,505,407" href="#147" title="Театральная" alt="Театральная"/>
<area shape="rect" coords="401,388,419,407" href="#148" title="Охотный ряд" alt="Охотный ряд"/>
<area shape="rect" coords="376,376,424,387" href="#148" title="Охотный ряд" alt="Охотный ряд"/>
<area shape="rect" coords="343,394,392,419" href="#149" title="Александровский сад" alt="Александровский сад"/>
<area shape="rect" coords="222,403,297,419" href="#150" title="Смоленская2" alt="Смоленская2"/>
<area shape="rect" coords="310,398,332,420" href="#151" title="Арбатская1" alt="Арбатская1"/>
<area shape="rect" coords="286,394,340,403" href="#151" title="Арбатская1" alt="Арбатская1"/>
<area shape="rect" coords="333,419,354,437" href="#152" title="Арбатская2" alt="Арбатская2"/>
<area shape="rect" coords="286,438,343,450" href="#152" title="Арбатская2" alt="Арбатская2"/>
<area shape="rect" coords="117,449,133,463" href="#153" title="Багратионовская" alt="Багратионовская"/>
<area shape="rect" coords="11,445,120,460" href="#153" title="Багратионовская" alt="Багратионовская"/>
<area shape="rect" coords="70,497,157,515" href="#154" title="Парк победы" alt="Парк победы"/>
<area shape="rect" coords="83,481,164,496" href="#155" title="Кутузовская" alt="Кутузовская"/>
<area shape="rect" coords="133,462,179,478" href="#156" title="Фили" alt="Фили"/>
<area shape="rect" coords="376,431,431,455" href="#157" title="Библиотека им. Ленина" alt="Библиотека им. Ленина"/>
<area shape="rect" coords="369,420,388,437" href="#157" title="Библиотека им. Ленина" alt="Библиотека им. Ленина"/>
<area shape="rect" coords="350,436,371,455" href="#158" title="Боровицкая" alt="Боровицкая"/>
<area shape="rect" coords="310,456,376,469" href="#158" title="Боровицкая" alt="Боровицкая"/>
<area shape="rect" coords="225,420,308,434" href="#159" title="Смоленская1" alt="Смоленская1"/>
<area shape="rect" coords="0,423,81,442" href="#169" title="Славянский бульвар" alt="Славянский бульвар"/>
<area shape="rect" coords="171,501,191,523" href="#160" title="Студенческая" alt="Студенческая"/>
<area shape="rect" coords="144,518,219,532" href="#160" title="Студенческая" alt="Студенческая"/>
<area shape="rect" coords="89,423,104,437" href="#161" title="Филевский парк" alt="Филевский парк"/>
<area shape="rect" coords="105,416,161,436" href="#161" title="Филевский парк" alt="Филевский парк"/>
<area shape="rect" coords="73,408,89,423" href="#162" title="Пионерская" alt="Пионерская"/>
<area shape="rect" coords="86,403,153,414" href="#162" title="Пионерская" alt="Пионерская"/>
<area shape="rect" coords="28,337,120,353" href="#163" title="Молодёжная" alt="Молодёжная"/>
<area shape="rect" coords="17,302,83,329" href="#164" title="Крылатское" alt="Крылатское"/>
<area shape="rect" coords="17,269,73,295" href="#165" title="Строгино" alt="Строгино"/>
<area shape="rect" coords="15,240,75,267" href="#166" title="Мякинино" alt="Мякинино"/>
<area shape="rect" coords="28,210,138,229" href="#167" title="Волоколамская" alt="Волоколамская"/>
<area shape="rect" coords="23,162,66,195" href="#168" title="Митино" alt="Митино"/>
</map>
</body>
</html>