***PHP***
========================
Так как нет подходяшего встроенного метода в api amoCRM для решения поставленной задачи, то было это реализовано так: получаем список всех сделок - запоминаем в массив A, получаем список всех задач, отсеиваем завершённые задачи, получаем ID сделок на которые назначены эти задачи - запоминаем в массив B, удаляем элементы массива B из массива A, в массиве A остаются только ID сделок для который нет активных задач. Создаём новую задачу с текстом “Сделка без задачи” для всех сделок из массива A. Данное решение не подходит для работающей системы, запрашивать все сделки и все задачи - не разумно, так же есть ограничения в самом api.



***JavaScript / HTML***
========================
Решение второй задачи на JavaScript / HTML не было реализовано в силу слабых познаний в области JS, но суть задачи ясна. Скрипт получает json, затем циклом создаёт дом-элементы и заполняет их данными. 
