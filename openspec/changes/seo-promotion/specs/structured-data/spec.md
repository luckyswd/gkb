## Purpose

Разметка schema.org в JSON-LD. По ней поисковики и ИИ-ассистенты понимают, что сайт принадлежит ООО «ГазКотелБел» — официальному представителю производителя АТЕМ, какие модели он выпускает, по каким ценам и где их купить. Разметка нужна для расширенных сниппетов в Яндексе и Google.

## ADDED Requirements

### Requirement: Связный граф на каждой странице

Каждая HTML-страница SHALL содержать один блок `<script type="application/ld+json">` с `@graph`, в котором есть:

- `Organization` (`@id` `https://gazkotelbel.com/#organization`): название, юридическое название, логотип, адрес, телефоны, email, УНП (`taxID`), соцсети (`sameAs`), `areaServed` «Беларусь». Данные берутся из глобальных настроек.
- `WebSite` (`@id` `/#website`) с `publisher` → организация, `inLanguage` `ru-BY` и `potentialAction` SearchAction на `/catalog/?search={search_term_string}`.
- `WebPage` (`@id` `{canonical}#webpage`) с `isPartOf` → сайт, `name`, `description`, `inLanguage`, `dateModified`.

Сущность `Person` для сайта или организации MUST NOT выводиться. Значения в JSON-LD MUST совпадать с видимыми на странице.

#### Scenario: Организация вместо Person

- **WHEN** валидатор schema.org разбирает главную
- **THEN** он находит `Organization` с названием «ГазКотелБел» и не находит `Person`

#### Scenario: Данные организации меняются в админке

- **WHEN** редактор меняет телефон в глобальных настройках
- **THEN** телефон в JSON-LD на всех страницах меняется после сброса кеша

### Requirement: Хлебные крошки

Каждая страница, кроме главной, SHALL содержать `BreadcrumbList`, который совпадает с видимыми хлебными крошками. Последний элемент — текущая страница.

#### Scenario: Крошки товара

- **WHEN** открыт товар «Житомир-10» из категории «Газовые котлы»
- **THEN** BreadcrumbList содержит «Главная» → «Вся продукция» → «Газовые котлы» → «ЖИТОМИР-10», как видимые крошки

### Requirement: Product на странице товара

Страница товара SHALL содержать `Product` со свойствами `name`, `description`, `image` (все изображения галереи), `brand` (`Brand` «АТЕМ»), `manufacturer` → организация, `category` и `url`. Если у товара заданы цены модификаций, Product SHALL содержать `offers` типа `AggregateOffer` с `priceCurrency` `BYN`, `lowPrice`, `highPrice`, `offerCount`, `availability` и `priceValidUntil` (дата следующего пересмотра прайса), а также `seller` → организация. Без цен `offers` не выводится. `aggregateRating` и `review` SHALL выводиться только при наличии реальных отзывов на странице.

#### Scenario: Товар с ценами

- **WHEN** у «Житомир-3» заданы модификации от 1 338 до 4 614 BYN
- **THEN** Product содержит AggregateOffer с `lowPrice` 1338, `highPrice` 4614, `priceCurrency` BYN

#### Scenario: Проверка расширенных результатов

- **WHEN** URL товара проверяют в Google Rich Results Test
- **THEN** «Товар» определяется без ошибок

#### Scenario: Нет выдуманного рейтинга

- **WHEN** у товара нет отзывов
- **THEN** в разметке нет `aggregateRating`

### Requirement: Списки товаров

Каталог и страницы категорий SHALL содержать `CollectionPage` с `mainEntity` типа `ItemList`. В списке перечислены товары страницы с `position`, `url` и `name` в том порядке, в котором они показаны.

#### Scenario: Категория

- **WHEN** открыта категория «Газовые котлы» с пятью товарами
- **THEN** ItemList содержит пять элементов со ссылками на эти товары

### Requirement: FAQ

Если на странице виден блок вопросов и ответов, она SHALL содержать `FAQPage` с теми же вопросами и ответами, слово в слово. Страница без видимого FAQ MUST NOT содержать FAQPage.

#### Scenario: FAQ категории

- **WHEN** у категории заполнено четыре вопроса
- **THEN** на странице видны четыре вопроса, а FAQPage содержит те же четыре пары

### Requirement: Статьи

Страница статьи SHALL содержать `Article` с `headline`, `description`, `image`, `datePublished`, `dateModified`, `author` и `publisher` (организация), а также `mainEntityOfPage`.

#### Scenario: Статья

- **WHEN** открыта статья «Как рассчитать мощность газового котла»
- **THEN** на ней есть Article с датами публикации и изменения

### Requirement: Региональные страницы и дилеры

Страница «Где купить в {области}» SHALL содержать `ItemList` из точек продаж, каждая с типом `Store` (название, адрес, телефон, координаты, если они есть на карте).

#### Scenario: Брестская область

- **WHEN** открыта страница дилеров Брестской области
- **THEN** ItemList содержит каждого дилера области как Store с адресом и телефоном

### Requirement: Разметка валидна

Весь JSON-LD SHALL разбираться как корректный JSON. Валидатор schema.org MUST NOT находить в нём ошибок. Английская версия страницы SHALL использовать `inLanguage` `en`.

#### Scenario: Проверка аудита

- **WHEN** скрипт аудита проходит по всем URL из sitemap
- **THEN** у каждой страницы ровно один разбираемый блок JSON-LD с Organization, WebSite и WebPage
