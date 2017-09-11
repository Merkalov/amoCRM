<?php
error_reporting(-1);
$root=__DIR__.DIRECTORY_SEPARATOR;

require $root .'Config.php';
require $root .'auth.php';

//функция для получения данных. Списка всех текущих задач и всех сделок. В будующем для контактов.
function getData($link)
{
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt');
    curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

    $response = curl_exec($curl);
    curl_close($curl);

    $response = json_decode($response, true);

    $response = array_shift($response); //разбираем многомерный массив
    $response = array_shift($response); //разбираем его

    return $response;
}

//Получаем список всех сделок.
$link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/leads/list';
$response = getData($link);
$leadsID = [];
foreach ($response as $res) {
    $leadsID[] = $res['id'];  //Заполняем массив их уникальными ID.
}


//Получаем список всех задач.
$link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/tasks/list';
$response = getData($link);
$idLeadsWithTasks = [];
foreach ($response as $task) {
    if ($task['status'] == 0) { //Проверяем статус задачи. Статус 0 - не завершена, 1- заверешена. Нам нужны не заверщенные.
        $idLeadsWithTasks[] = $task['element_id']; //Запоминаем в массив ID сделок для которых эти задачи стоят.
    }
}

//Сравниваем
foreach ($leadsID as $key=>$id) {
    foreach ($idLeadsWithTasks as $idTask) {
        if ($id == $idTask) {                   //Если из списка всех сделок ID совпадает с ID сделки у которой есть задачи
            unset($leadsID[$key]);              //удаляем. В массиве остаются только ID сделок у которй нет задач.
        }
    }
}


//Добавляем задачи для сделок у которых их нет.
$tasks['request']['tasks']['add'] = [];
if (!empty($leadsID)){
    foreach ($leadsID as $key=>$id){
        array_push($tasks['request']['tasks']['add'],
            array(
                'element_id'=>$id,
                'element_type'=>2,
                'task_type'=>3,
                'text'=>'Сделка без задачи',
                'responsible_user_id'=> $responsibleUserID,
                'complete_till'=>1505199999
            )
        );
    }

    $link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/tasks/set';
    $curl=curl_init();
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
    curl_setopt($curl,CURLOPT_URL,$link);
    curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
    curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($tasks));
    curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
    curl_setopt($curl,CURLOPT_HEADER,false);
    curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt');
    curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt');
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

    $out=curl_exec($curl);

    $Response=json_decode($out,true);

    echo 'Для всех сделок без открытых задач была создана новая задача с текстом “Сделка без задачи”.';

}
else{
    die('Нет сделок без задач');
}
