<?php

use buildsys\library\event\manager\action\event as eventBuild;

include('common/head.php');

eventBuild::rmBrunch(null, null);
eventBuild::createFolder(null, null);
eventBuild::createItems(null, null);

//print "\n<br style='clear:both'/>Use:".''.memory_get_usage().'<br/>Max:'.memory_get_peak_usage().'<br/>';