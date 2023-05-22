<?php

// Simple library that holds all the links for the admin cp

// CAT_ID => array(  PAGE_ID  => (PAGE_NAME, URL ) )

// $PAGES[ $cat_id ][$page_id][0] = Page name
// $PAGES[ $cat_id ][$page_id][1] = Url

 
$PAGES = array(

				/*0 => array (
							 1 => array( 'Новости IPS'         , 'act=ips&code=news'   ),
							 2 => array( 'Проверка обновлений'      , 'act=ips&code=updates'  ),
							 3 => array( 'Документация'     , 'act=ips&code=docs'    ),
							 4 => array( 'Поддержка'       , 'act=ips&code=support' ),
							 5 => array( 'IPS хостинг'  , 'act=ips&code=host'   ),
							 6 => array( 'Платные услуги'    , 'act=ips&code=purchase'     ),
						   ),*/
						   
				1 => array (
				
							1 => array( 'IP чат'              , 'act=pin&code=ipchat'  ),
							2 => array( 'IPS хостинг'          , 'act=ips&code=host'    ),
							3 => array( 'Регистрация IPB'     , 'act=pin&code=reg'     ),
							4 => array( 'Удаление копирайтов IPB', 'act=pin&code=copy'    ),
							5 => array( 'Управление подписками'    , 'act=msubs' ),
							6 => array( '&#0124;-- Логи'          , 'act=msubs&code=searchlog', 'modules/subsmanager' ),
							7 => array( '&#0124;-- Настройка валют' , 'act=msubs&code=currency', 'modules/subsmanager' ),
							8 => array( '&#039;-- Сделки'   , 'act=msubs&code=dosearch', 'modules/subsmanager' ),
							
							
						   ),

				2 => array (
							 1 => array( 'Главная конфигурация', 'act=op&code=url'   ),
							 2 => array( 'Безопасность'      , 'act=op&code=secure'  ),
							 3 => array( 'Темы, Сообщения, Опросы', 'act=op&code=post'    ),
							 4 => array( 'Профиль пользователей'      , 'act=op&code=avatars' ),
							 5 => array( 'Формат даты и времени'  , 'act=op&code=dates'   ),
							 6 => array( 'Экономия CPU'    , 'act=op&code=cpu'     ),
							 7 => array( 'Cookies'       , 'act=op&code=cookie'  ),
							 8 => array( 'Настройки PM'       , 'act=op&code=pm'    ),
							 9 => array( 'Вкл/Выкл форума'    , 'act=op&code=board' ),
							 10 =>array( 'Настройка новостей'    , 'act=op&code=news' ),
							 11 =>array( 'Календарь/Именинники'    , 'act=op&code=calendar' ),
							 12 =>array( 'Настройка COPPA'       , 'act=op&code=coppa' ),
							 13 =>array( 'IBF портал'         , 'act=op&code=portal' ),
							 14 =>array( 'Настройка Email'       , 'act=op&code=email' ),
							 15 =>array( 'Данные сервера' , 'act=op&code=phpinfo' ),
							 16 =>array( 'Руководства форума'   , 'act=op&code=glines' ),
							 17 =>array( 'Полнотекстовый поиск', 'act=op&code=fulltext'),
							 18 =>array( 'Поисковые машины', 'act=op&code=spider' ),
							 19 =>array( 'Настройка рейтинга'       , 'act=op&code=warn' ),
							 20 =>array( 'Настройка IPDynamic Lite'    , 'act=csite', 'sources/dynamiclite' ),
							 21 =>array( 'Настройка Online/Offline'   , 'act=sonline' ),
							 ),

				3 => array (
							 1 => array( 'Новая категория'        , 'act=cat&code=new'        ),
							 2 => array( 'Новый форум'           , 'act=forum&code=newsp'    ),
							 3 => array( 'Управление форумами'    , 'act=cat&code=edit'       ),
							 4 => array( 'Маски доступа'    , 'act=group&code=permsplash'),
							 5 => array( 'Пересортировка категорий' , 'act=cat&code=reorder'    ),
							 6 => array( 'Пересортировка форумов'     , 'act=forum&code=reorder'  ),
							 7 => array( 'Модераторы'          , 'act=mod'                 ),
							 //8 => array( 'Мульти-модерация тем', 'act=multimod'          ),
						   ),
						   
				
				4 => array (
							 1 => array( 'Панель модераторов'       , 'act=modcp'     , 1     ),
							 2 => array( 'Мульти-модерация тем', 'act=multimod'          ),
						   ),
						   
						   
				5 => array (
							1 => array ( 'Предв. регистрация'        , 'act=mem&code=add'  ),
							2 => array ( 'Поиск/Редакт/Блок пользов.'      , 'act=mem&code=edit' ),
							3 => array ( 'Удаление пользователей'      , 'act=mem&code=del'  ),
							4 => array ( 'Список заблокированных', 'act=mem&code=advancedsearch&showsusp=1' ),
							5 => array ( 'БАН пользователей'        , 'act=mem&code=ban'  ),
							6 => array ( 'Статусы пользователей'    , 'act=mem&code=title'),
							7 => array ( 'Управление группами'  , 'act=group'         ),
							8 => array ( 'Подтверждение рег-ций', 'act=mem&code=mod'  ),
							9 => array ( 'Доп-ные поля профиля', 'act=field'       ),
							10 => array ( 'Массовая Email рассылка'   , 'act=mem&code=mail' ),
							11 => array ( 'Пользовательские средства'         , 'act=mtools'  ),
							12 => array ( 'Пользователи'  , 'act=massive' ),
							13 => array ( 'Показ цветов групп'   , 'act=shadow' ),
							
						   ),
						   
				6 => array (
							1 => array( 'Фильтр нецензурных слов', 'act=op&code=bw'    ),
							2 => array( 'Настройка смайликов', 'act=op&code=emo'   ),
							3 => array( 'Настройка помощи', 'act=help'         ),
							4 => array( 'Пересчёт статистики', 'act=op&code=count'    ),
							
						   ),
						   
				7 => array (
							1 => array( '<b>Настройка скинов</b>' , 'act=sets'        ),
							2 => array( '&#0124;-- Шаблоны форума'   , 'act=wrap'        ),
							3 => array( '&#0124;-- HTML шаблоны'   , 'act=templ'       ),
							4 => array( '&#0124;-- Стили'    , 'act=style'       ),
							5 => array( '&#039;-- Макросы'           , 'act=image'       ),
							6 => array( 'Импорт скинов'       , 'act=import'      ),
							7 => array( 'Проверка версии скина'    , 'act=skinfix'      ),
							
						   ),
						   
				8 => array (
							1 => array( 'Настройка языков' , 'act=lang'             ),
							2 => array( 'Импортирование языка', 'act=lang&code=import' ),
						   ),
						   
				9 => array (
							1 => array( 'Статистика регистрации' , 'act=stats&code=reg'   ),
							2 => array( 'Статистика новых тем'    , 'act=stats&code=topic' ),
							3 => array( 'Статистика сообщений'         , 'act=stats&code=post'  ),
							4 => array( 'Статистика PM'    , 'act=stats&code=msg'   ),
							5 => array( 'Просмотры тем'        , 'act=stats&code=views' ),
						   ),
						   
				10 => array (
							1 => array( 'Средства mySQL'   , 'act=mysql'           ),
							2 => array( 'Резервная копия mySQL'   , 'act=mysql&code=backup'    ),
							3 => array( 'Информация SQL Runtime', 'act=mysql&code=runtime'   ),
							4 => array( 'Переменные SQL' , 'act=mysql&code=system'    ),
							5 => array( 'Процессы SQL'   , 'act=mysql&code=processes' ),
						   ),
				
				11 => array(
							1 => array( 'Логи модераторов', 'act=modlog'    ),
							2 => array( 'Логи админов'    , 'act=adminlog'  ),
							3 => array( 'Email логи'    , 'act=emaillog'  ),
							4 => array( 'Логи ботов'      , 'act=spiderlog' ),
							5 => array( 'Логи рейтинга'     , 'act=warnlog'   ),
						   ),
						   
			   12 => array(
							1 => array( 'Информация' , 'act=downloads'),
							2 => array( 'Настройки' , 'act=downloads&code=settings'),
							3 => array( 'Создать категорию' , 'act=downloads&code=showaddcat'),
							4 => array( 'Редактировать категории' , 'act=downloads&code=showeditcat'),
							5 => array( 'Удалить категорию' , 'act=downloads&code=showdelcat'),
							6 => array( 'Пересортировка категорий' , 'act=downloads&code=reorder'),
							7 => array( 'Дополнительные поля' , 'act=dfield'),
							8 => array( 'Вкл/Выкл архива' , 'act=downloads&code=switch'),
						),
						
			   );
			   
			   
$CATS = array (   
				  //0 => "IPS сервис",
				  1 => "Расширения IPB",
				  2 => "Системные настройки",
			      3 => 'Настройки форумов',
			      4 => 'Модерирование форума',
				  5 => 'Пользователи и группы',
				  6 => 'Администрирование',
				  7 => 'Скины и Шаблоны',
				  8 => 'Языки',
				  9 => 'Центр статистики',
				  10 => 'Управление SQL',
				  11 => 'Логи форума',
				  12 => 'Файловый архив',
			  );
			  
$DESC = array (
				 // 0 => "Самые последние новости, документации, запросы поддержки, приобретение доп. услуг и т.д.",
				  1 => "Установка и настройка различных плагинов для форума",
				  2 => "Основные настройки форума, такие как cookies, безопасность, атрибуты сообщений и т.д.",
				  3 => "Создание, редактирование, удаление категорий, форумов, модераторов",
				  4 => "Вход в панель модерирования и мульти-модерация тем",
				  5 => "Редактирование, регистрация, удаление, бан пользователей. Настройка статусов. Управление группами и т.д.",
				  6 => "Настройки файлов помощи, фильтра нецензурных слов и смайликов",
				  7 => "Настройки цветов, шаблонов, скинов и изображений.",
				  8 => "Настройки языков",
				  9 => "Статистика регистраций и сообщений",
				  10 => "Управление Вашей SQL базой; правка, оптимизация, экспорт базы и т.д.",
				  11 => "Просмотр логов админов, модераторов и т.д. (Только для админов)",
				  12 => "Управление файловым архивом",
			  );
			  
			  
?>