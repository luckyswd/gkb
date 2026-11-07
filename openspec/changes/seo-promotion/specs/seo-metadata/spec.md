## Purpose

Мета-теги каждой страницы сайта: title, description, canonical, директивы robots, Open Graph и Twitter Card. Они формируются по шаблонам на каждый тип страницы, редактор может переопределить их, а английская версия и служебные страницы закрыты от индексации.

## ADDED Requirements

### Requirement: Единственный источник мета-тегов

Каждая HTML-страница сайта SHALL содержать ровно один `<title>`, не больше одного `<meta name="description">`, ровно один `<link rel="canonical">` и ровно один `<meta name="robots">`. Мета-теги MUST NOT дублироваться сторонним SEO-плагином или ядром WordPress.

#### Scenario: Нет дублей в head

- **WHEN** загружается любая страница из sitemap
- **THEN** в `<head>` один title, один canonical, один meta robots и не больше одного meta description

#### Scenario: Плагин SEO отключён

- **WHEN** All in One SEO деактивирован
- **THEN** все мета-теги, Open Graph и JSON-LD продолжают выводиться темой без изменений

### Requirement: Шаблоны title

Title SHALL формироваться по шаблону своего типа страницы, если редактор не задал свой title в поле SEO:

- главная: «Газовые котлы Житомир — официальный представитель АТЕМ в Беларуси»;
- категория: «{Название категории} Житомир — купить в Беларуси | ГазКотелБел»;
- товар: «{Название товара} — {подзаголовок} | ГазКотелБел». Цены в title не выводятся: они публикуются только в прайс-листе;
- статья: «{Заголовок статьи} | ГазКотелБел»;
- прочие страницы: «{Заголовок страницы} | ГазКотелБел».

В title MUST NOT быть повторов бренда, хвоста «gazkotelbel» и пустых разделителей. Длина title SHOULD укладываться в 70 символов. Если шаблон не помещается, бренд в конце отбрасывается раньше содержательной части.

#### Scenario: Title товара

- **WHEN** открыта страница «Житомир-3»
- **THEN** title начинается с «ЖИТОМИР-3», содержит подзаголовок и не содержит цен

#### Scenario: Ручной title

- **WHEN** редактор заполнил поле «SEO title» у страницы «О производстве»
- **THEN** title страницы равен заполненному значению без добавлений

#### Scenario: Нет мусорного хвоста

- **WHEN** открыта любая страница сайта
- **THEN** title не содержит подстроку «gazkotelbelgazkotelbel» и не заканчивается на « -»

### Requirement: Description на каждой индексируемой странице

Каждая индексируемая страница SHALL иметь meta description длиной 120–170 символов. Description берётся из поля SEO, если редактор его заполнил. Иначе он строится по шаблону типа страницы. Шаблон товара включает мощность или диапазон мощностей и «официальная гарантия». Цены в description не выводятся. Шаблон категории включает число моделей и диапазон мощностей. Две индексируемые страницы MUST NOT иметь одинаковый description.

#### Scenario: Description категории без ручного текста

- **WHEN** у категории «Газовые котлы» не заполнено поле SEO description
- **THEN** description страницы категории сгенерирован по шаблону и содержит число моделей

#### Scenario: Уникальность

- **WHEN** скрипт аудита собирает description всех URL из sitemap
- **THEN** повторяющихся значений нет

### Requirement: Канонический адрес

Canonical SHALL указывать на абсолютный адрес страницы на `https://gazkotelbel.com` с завершающим слешем, без параметров запроса. Запросы с UTM-метками, `?lang=`, параметрами сортировки и пагинации каталога SHALL указывать canonical на чистый адрес.

#### Scenario: UTM-метки

- **WHEN** открыт `https://gazkotelbel.com/products/zhytomyr-3/?utm_source=yandex`
- **THEN** canonical равен `https://gazkotelbel.com/products/zhytomyr-3/`

### Requirement: Закрытие английской версии от индексации

Страница, отданная на английском (по куке `lang=en` или параметру `?lang=en`), SHALL содержать `<meta name="robots" content="noindex, follow">` и отдаваться с заголовком `X-Robots-Tag: noindex, follow`. Русская версия того же адреса MUST NOT получать noindex, в том числе когда отдаётся из кеша страниц.

#### Scenario: Английская страница

- **WHEN** запрошен `https://gazkotelbel.com/products/zhytomyr-10/?lang=en`
- **THEN** ответ содержит `X-Robots-Tag: noindex, follow`, а в head — meta robots `noindex, follow`

#### Scenario: Русская копия из кеша

- **WHEN** после посещения английской версии запрошен тот же адрес без куки
- **THEN** ответ не содержит noindex ни в заголовке, ни в head

### Requirement: Служебные страницы не индексируются

Страницы входа, регистрации и восстановления пароля, выдача поиска по сайту (`?s=`, `/catalog/?search=`), страницы вложений, ленты RSS, архивы авторов и дат SHALL отдаваться с `noindex, follow` или перенаправляться 301 на содержательную страницу. Прочие страницы SHALL иметь `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1`.

#### Scenario: Страница входа

- **WHEN** запрошен `/login/`
- **THEN** в head meta robots `noindex, follow`

#### Scenario: Выдача поиска

- **WHEN** запрошен `/catalog/?search=житомир`
- **THEN** в head meta robots `noindex, follow`

### Requirement: Open Graph и Twitter Card

Каждая индексируемая страница SHALL содержать `og:type`, `og:site_name` («ГазКотелБел»), `og:locale` (`ru_BY`), `og:title`, `og:description`, `og:url` (равен canonical), `og:image` с `og:image:width`, `og:image:height` и `og:image:alt`, а также `twitter:card=summary_large_image`. Для товара `og:image` — его главное фото, для остальных страниц — изображение по умолчанию 1200×630 из глобальных настроек. На странице товара `og:type` SHALL быть `product`, на статье — `article`.

#### Scenario: Публикация ссылки на товар в мессенджере

- **WHEN** ссылку на `/products/zhytomyr-turbo/` вставляют в Telegram или Viber
- **THEN** превью показывает название товара, описание и фото товара

### Requirement: Редактирование SEO-полей

У страниц, товаров, категорий товаров и статей SHALL быть поля «SEO title», «SEO description», «Изображение для соцсетей» и флаг «Скрыть из поиска (noindex)». Пустое поле означает «использовать шаблон». Флаг noindex SHALL также исключать страницу из sitemap и llms.txt.

#### Scenario: Редактор скрывает страницу

- **WHEN** редактор включает «Скрыть из поиска» у страницы и сохраняет её
- **THEN** страница отдаётся с `noindex, follow` и пропадает из sitemap
