## Purpose

Управление тем, что видят и индексируют роботы: robots.txt, sitemap, редиректы с мусорных адресов, мгновенные уведомления поисковиков об изменениях и подключение сайта к Яндекс Вебмастеру и Google Search Console.

## ADDED Requirements

### Requirement: robots.txt

`/robots.txt` SHALL отдаваться темой с кодом 200 и `Content-Type: text/plain` и содержать:

- для всех роботов: запрет `/wp-admin/` (кроме `/wp-admin/admin-ajax.php`), `/wp-login.php`, `/?s=`, `/*?search=`, `/login/`, `/registration/`, `/feed/`, `/wp-content/uploads/wpo-plugins-tables-list.json`;
- директиву `Clean-param: utm_source&utm_medium&utm_campaign&utm_content&utm_term&yclid&gclid&fbclid` для Яндекса;
- одну строку `Sitemap: https://gazkotelbel.com/sitemap.xml`;
- комментарий со ссылкой на `/llms.txt`.

CSS, JS и изображения темы и загрузок MUST оставаться доступными для роботов. Адреса с `?lang=` MUST NOT запрещаться в robots.txt: иначе робот не увидит на них noindex.

#### Scenario: Проверка в Вебмастере

- **WHEN** Яндекс Вебмастер анализирует robots.txt
- **THEN** ошибок нет, главная и страницы товаров разрешены, `/login/` запрещена

#### Scenario: Ресурсы страниц открыты

- **WHEN** Googlebot загружает CSS из `/wp-content/themes/gkb-theme/dist/`
- **THEN** robots.txt этого не запрещает

### Requirement: Sitemap

`/sitemap.xml` SHALL быть индексом карт: страницы, товары, категории товаров, региональные страницы дилеров. Каждая вложенная карта перечисляет только индексируемые русские адреса с кодом 200, равные своим canonical, с `<lastmod>` по дате последнего изменения записи. Для товаров в карте SHALL быть `<image:image>` главного изображения. В sitemap MUST NOT попадать страницы с noindex, английские адреса, редиректы, вложения, страницы входа и регистрации. Старые адреса карт (`/wp-sitemap.xml`, `/sitemap_index.xml`, `/post-sitemap.xml`, `/page-sitemap.xml`, `/products-sitemap.xml`, `/product-category-sitemap.xml`, `/category-sitemap.xml`, `/sitemap.rss`) SHALL перенаправляться 301 на `/sitemap.xml` или отдавать новую карту того же содержания.

#### Scenario: Состав sitemap

- **WHEN** скрипт аудита разбирает `/sitemap.xml` и все вложенные карты
- **THEN** каждый URL отвечает 200, его canonical совпадает с ним самим, и среди них нет `/login/`, `/registration/` и «Привет, мир!»

#### Scenario: Новый товар

- **WHEN** редактор публикует новый товар
- **THEN** его адрес появляется в карте товаров с lastmod, равным дате публикации

### Requirement: Чистка мусорных адресов

Запись «Привет, мир!» и рубрика «Без рубрики» SHALL быть удалены. Их адреса, вложения товаров и архивы авторов SHALL отвечать 301 на содержательную страницу: главную, каталог или родительскую запись. Адрес без завершающего слеша, `www.`-адрес и `http://`-адрес SHALL одним 301 вести на канонический `https://gazkotelbel.com/…/`, без цепочек редиректов.

#### Scenario: Тестовая запись

- **WHEN** запрошен адрес записи «Привет, мир!»
- **THEN** ответ 301 на главную

#### Scenario: Один редирект

- **WHEN** запрошен `http://www.gazkotelbel.com/catalog`
- **THEN** не больше одного редиректа ведёт на `https://gazkotelbel.com/catalog/`

### Requirement: Несуществующие адреса

Несуществующий адрес SHALL отвечать кодом 404 со страницей 404 темы. Он MUST NOT отвечать кодом 200 или редиректом на главную.

#### Scenario: Опечатка в адресе

- **WHEN** запрошен `/products/net-takogo/`
- **THEN** ответ 404

### Requirement: IndexNow

Сайт SHALL отдавать файл ключа IndexNow по адресу `/{ключ}.txt`. При публикации, изменении, снятии с публикации или удалении товара, категории товаров или страницы, если её адрес индексируемый, сайт SHALL в течение минуты отправлять этот адрес в IndexNow (`yandex.com/indexnow`). Ошибки отправки MUST NOT мешать сохранению записи. Они пишутся в журнал.

#### Scenario: Правка товара

- **WHEN** редактор сохраняет изменения в «Житомир-М»
- **THEN** адрес `https://gazkotelbel.com/products/zhytomyr-m/` уходит в IndexNow, а в журнале видно, что ответ 200 или 202

#### Scenario: IndexNow недоступен

- **WHEN** сервис IndexNow не отвечает
- **THEN** запись сохраняется как обычно, ошибка попадает в журнал

### Requirement: Подключение к вебмастерам

Сайт SHALL быть подтверждён в Яндекс Вебмастере и Google Search Console. В обоих сервисах SHALL быть добавлен `/sitemap.xml`, в Вебмастере — регион «Беларусь» и адрес организации в Яндекс Бизнесе. Метатеги подтверждения выводятся только на боевом домене.

#### Scenario: Search Console

- **WHEN** владелец открывает Search Console для `https://gazkotelbel.com/`
- **THEN** ресурс подтверждён, sitemap принят без ошибок
