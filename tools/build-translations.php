<?php
/**
 * Translation builder for the CosyPaw theme.
 *
 * Generates languages/{en_US,ru_RU,sr_RS}.{po,mo} from the maps below. Source
 * (msgid) strings are mostly Serbian (front-end) with some English (admin).
 *
 *   - Default site (sr): Serbian msgids fall through untranslated; only the
 *     English-source admin strings get an sr_RS translation.
 *   - en_US: Serbian msgids -> English (English msgids fall through unchanged).
 *   - ru_RU: everything -> Russian.
 *
 * Run with any PHP 8.x:  php tools/build-translations.php
 * No gettext/msgfmt needed — .mo is packed directly.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

// Serbian-source msgid => [ english, russian ].
$sr_source = array(
	'CosyPaw žurnal'                                                                          => array( 'CosyPaw journal', 'Журнал CosyPaw' ),
	'Prethodna'                                                                               => array( 'Previous', 'Предыдущая' ),
	'Sledeća'                                                                                 => array( 'Next', 'Следующая' ),
	'Ova stranica se sakrila'                                                                 => array( 'This page is hiding', 'Эта страница спряталась' ),
	'Stranicu nismo našli — možda je premeštena. Probaj pretragu ili se vrati na početnu.'    => array( "We couldn't find the page — it may have moved. Try a search or head back home.", 'Мы не нашли страницу — возможно, она перемещена. Попробуйте поиск или вернитесь на главную.' ),
	'Nazad na početnu'                                                                        => array( 'Back to home', 'На главную' ),
	'Pretraga'                                                                                => array( 'Search', 'Поиск' ),
	'Pretraži…'                                                                               => array( 'Search…', 'Поиск…' ),
	'Traži'                                                                                   => array( 'Search', 'Найти' ),
	'Mekani svet peškirića'                                                                   => array( 'A soft world of little towels', 'Мягкий мир полотенчиков' ),
	'Dečiji peškiri od %s'                                                                    => array( 'Children’s towels made of %s', 'Детские полотенца из %s' ),
	'mikrofibera'                                                                             => array( 'microfiber', 'микрофибры' ),
	'Izaberi paket'                                                                           => array( 'Choose a package', 'Выбери набор' ),
	'Pogledaj peškiriće'                                                                      => array( 'See the designs', 'Смотреть мотивы' ),
	'Ručni rad'                                                                               => array( 'Handmade', 'Ручная работа' ),
	'Izdvojeni peškirići'                                                                     => array( 'Featured designs', 'Избранные мотивы' ),
	'od %s'                                                                                   => array( 'from %s', 'от %s' ),
	'Zašto CosyPaw'                                                                           => array( 'Why CosyPaw', 'Почему CosyPaw' ),
	'Mali zagrljaj pored lavaboa'                                                             => array( 'A little hug by the sink', 'Маленькое объятие у раковины' ),
	'Svaki peškirić je mekan, upijajuć i ima alku za kačenje — uvek pri ruci, uvek sladak.'   => array( 'Every towel is soft, absorbent and has a hanging loop — always at hand, always cute.', 'Каждое полотенце мягкое, впитывающее и с петелькой — всегда под рукой, всегда милое.' ),
	// "Zašto CosyPaw" cards. The four they replace were feature-talk, and three
	// of the four facts are already spent above: the hero lead carries the
	// microfibre and the hanging loop, the trust strip carries the handwork.
	'Deca ih biraju sama'                                                                     => array( 'Children pick them out themselves', 'Дети выбирают их сами' ),
	'Ruke se obrišu bez pregovora kad na kuki visi drugar.'                                   => array( 'Hands get dried without an argument when a friend is hanging on the hook.', 'Руки вытираются без уговоров, когда на крючке висит друг.' ),
	'Upija, ne samo ukrašava'                                                                 => array( 'It absorbs, it does not just decorate', 'Впитывает, а не только украшает' ),
	'Plišana mikrofibra osuši ručice u trenu i ostane sveža do večeri.'                       => array( 'Plush microfiber dries little hands in an instant and stays fresh till evening.', 'Плюшевая микрофибра сушит ручки мгновенно и остаётся свежей до вечера.' ),
	'Šiven rukom, jedan po jedan'                                                             => array( 'Sewn by hand, one at a time', 'Сшито вручную, по одному' ),
	'Isečen, šiven i pregledan ručno. Nema dva potpuno ista.'                                 => array( 'Cut, sewn and checked by hand. No two are exactly alike.', 'Раскроено, сшито и проверено вручную. Нет двух совершенно одинаковых.' ),
	'Poklon koji se pamti'                                                                    => array( 'A gift that is remembered', 'Подарок, который запоминается' ),
	'Sitnica koja izmami osmeh pre nego što je odmotana.'                                     => array( 'A little thing that draws a smile before it is even unwrapped.', 'Мелочь, что вызывает улыбку ещё до того, как её развернут.' ),
	'Što više peškirića, veća ušteda'                                                            => array( 'The more towels, the bigger the saving', 'Чем больше полотенец, тем больше выгода' ),
	'Izmešaj peškiriće kako želiš — cena po komadu pada sa svakim sledećim.'                  => array( 'Mix the designs however you like — the price per piece drops with each one.', 'Сочетай мотивы как хочешь — цена за штуку падает с каждым следующим.' ),
	'kom'                                                                                     => array( 'pc', 'шт' ),
	'Besplatna dostava'                                                                       => array( 'Free shipping', 'Бесплатная доставка' ),
	'Ušteda %s'                                                                               => array( 'Save %s', 'Экономия %s' ),
	'Dodaj u korpu'                                                                           => array( 'Add to cart', 'В корзину' ),
	'Plaćanje pouzećem • Dostava 2–4 radna dana'                                              => array( 'Cash on delivery • Delivery in 2–4 business days', 'Оплата при получении • Доставка 2–4 рабочих дня' ),
	'Cela družina'                                                                            => array( 'The whole crew', 'Вся компания' ),
	'Upoznaj sve peškiriće'                                                                   => array( 'Meet all the designs', 'Познакомься со всеми мотивами' ),
	'Dečiji peškiri sa životinjicama, zalogajčićima i cvetićima, od %s po komadu — ili ih spoji u paket i uštedi.' => array( 'Children’s towels with little animals, treats and flowers, from %s each — or bundle them and save.', 'Детские полотенца со зверятами, вкусняшками и цветочками, от %s за штуку — или собери в набор и сэкономь.' ),
	'%s • 1 kom'                                                                              => array( '%s • 1 pc', '%s • 1 шт' ),
	'Korpa'                                                                                   => array( 'Cart', 'Корзина' ),
	'Kupi'                                                                                    => array( 'Buy', 'Купить' ),
	'Prikaži sve peškiriće (%d)'                                                              => array( 'Show all designs (%d)', 'Показать все мотивы (%d)' ),
	'Prikaži manje'                                                                           => array( 'Show less', 'Свернуть' ),
	'%1$d+%2$d GRATIS'                                                                        => array( '%1$d+%2$d FREE', '%1$d+%2$d В ПОДАРОК' ),
	'Dodato u korpu'                                                                          => array( 'Added to cart', 'Добавлено в корзину' ),
	'Dodato'                                                                                  => array( 'Added', 'Добавлено' ),
	'Rezultati za „%s”'                                                                       => array( 'Results for “%s”', 'Результаты по запросу «%s»' ),
	'Mekani svet peškirića. Ručno šiveni ljubimci koji čine svako kupatilo toplijim.'         => array( 'A soft world of little towels. Hand-sewn pets that make every bathroom warmer.', 'Мягкий мир полотенчиков. Сшитые вручную зверушки, что делают любую ванную теплее.' ),
	'Brzi linkovi'                                                                            => array( 'Quick links', 'Быстрые ссылки' ),
	'Paketi'                                                                                  => array( 'Packages', 'Наборы' ),
	'Svi peškirići'                                                                           => array( 'All designs', 'Все мотивы' ),
	'Poručivanje'                                                                             => array( 'Ordering', 'Заказ' ),
	'Poruči preko DM-a ili korpe.<br>Plaćanje pouzećem.'                                      => array( 'Order via DM or the cart.<br>Cash on delivery.', 'Заказывай через DM или корзину.<br>Оплата при получении.' ),
	'© %d CosyPaw — Mekani svet peškirića'                                                    => array( '© %d CosyPaw — A soft world of little towels', '© %d CosyPaw — Мягкий мир полотенчиков' ),
	'Ručni rad • Made with love'                                                              => array( 'Handmade • Made with love', 'Ручная работа • Сделано с любовью' ),
	'Zatvori korpu'                                                                           => array( 'Close cart', 'Закрыть корзину' ),
	'Tvoja korpa'                                                                             => array( 'Your cart', 'Твоя корзина' ),
	'Zatvori'                                                                                 => array( 'Close', 'Закрыть' ),
	'Korpa je još prazna'                                                                     => array( 'Your cart is still empty', 'Корзина пока пуста' ),
	'Izaberi paket ili omiljeni peškirić.'                                                    => array( 'Pick a package or a favorite design.', 'Выбери набор или любимый мотив.' ),
	'Ukupno'                                                                                  => array( 'Total', 'Итого' ),
	'Nastavi ka plaćanju'                                                                     => array( 'Proceed to checkout', 'Перейти к оплате' ),
	'Ručni rad sa puno ljubavi'                                                               => array( 'Handmade with lots of love', 'Ручная работа с большой любовью' ),
	'Porudžbine preko %s stižu uz besplatnu dostavu.'                                          => array( 'Orders over %s ship free.', 'Заказы свыше %s доставляются бесплатно.' ),
	'Besplatna dostava preko %s'                                                              => array( 'Free shipping over %s', 'Бесплатная доставка от %s' ),
	'Dečiji peškiri od mikrofibera, ručno rađeni, sa alkom za kačenje i preko 20 oblika. Sastavi svoj paket, plaćanje pouzećem, dostava 2–4 dana širom Srbije.' => array( 'Handmade children’s microfiber towels with a hanging loop, in over 20 shapes. Build your own bundle, cash on delivery, 2–4 day shipping across Serbia.', 'Детские полотенца из микрофибры ручной работы, с петелькой, более 20 форм. Собери свой набор, оплата при получении, доставка 2–4 дня по всей Сербии.' ),
	'Rezultati pretrage za „%s“.'                                                             => array( 'Search results for “%s”.', 'Результаты поиска по запросу «%s».' ),
	'CosyPaw dečiji peškiri od mikrofibera'                                                   => array( 'CosyPaw children’s microfiber towels', 'Детские полотенца CosyPaw из микрофибры' ),
	'Ručno rađeni dečiji peškiri od plišane mikrofibre, sa alkom za kačenje.'                 => array( 'Handmade children’s towels in plush microfiber, with a hanging loop.', 'Детские полотенца ручной работы из плюшевой микрофибры, с петелькой для подвешивания.' ),
	'Dečiji peškir od mikrofibera, oblik %s'                                                  => array( 'Children’s microfiber towel, %s shape', 'Детское полотенце из микрофибры, форма «%s»' ),

	// Front page, "O našim peškirima" — the page's one block of search copy.
	'O našim peškirima'                                                                       => array( 'About our towels', 'О наших полотенцах' ),
	'Ručno rađeni peškiri za decu — i za šape'                                                => array( 'Handmade towels for children — and for paws', 'Полотенца ручной работы для детей — и для лапок' ),
	'Tražiš dečije peškire koji su mekani, upijajući i dovoljno slatki da dete samo poželi da obriše ruke? CosyPaw peškiri od mikrofibera šiju se ručno, jedan po jedan. Plišana mikrofibra je nežna prema dečijoj koži, upija u trenu, brzo se suši i ostaje meka i posle mnogo pranja.' => array( 'Looking for children’s towels that are soft, absorbent and cute enough that a child wants to dry their hands? CosyPaw microfiber towels are sewn by hand, one at a time. Plush microfiber is gentle on a child’s skin, absorbs in an instant, dries fast and stays soft wash after wash.', 'Ищешь детские полотенца — мягкие, впитывающие и настолько милые, что ребёнок сам захочет вытереть руки? Полотенца CosyPaw из микрофибры шьются вручную, по одному. Плюшевая микрофибра нежна к детской коже, мгновенно впитывает, быстро сохнет и остаётся мягкой после многих стирок.' ),
	'Svaki peškir ima alku za kačenje, pa visi na dečijoj visini — pored lavaboa, na kuki ili na vratima. Kad na kuki čeka drugar, pranje ruku postaje igra, a dete samo bira svoj peškir i samo ga koristi. Izaberi između %1$s: životinjice poput zeke, sove, pande i kapibare, zalogajčići i cvetići.' => array( 'Every towel has a hanging loop, so it hangs at a child’s height — by the sink, on a hook or on a door. When a friend is waiting on the hook, washing hands becomes a game, and the child picks and uses their own towel. Choose from %1$s: little animals like the bunny, owl, panda and capybara, treats and flowers.', 'У каждого полотенца есть петелька, поэтому оно висит на высоте ребёнка — у раковины, на крючке или на двери. Когда на крючке ждёт друг, мытьё рук становится игрой, а ребёнок сам выбирает своё полотенце и сам им пользуется. Выбирай из %1$s: зверята — зайчик, сова, панда и капибара, вкусняшки и цветочки.' ),
	'%d jedinstvenih oblika'                                                                  => array( '%d unique shapes', '%d уникальных форм' ),
	'CosyPaw peškiri su i %1$s — za rođenje bebe, krštenje, prvi rođendan ili bebi šauer. Svaki paket stiže u CosyPaw kutiji, sa porukom dobrodošlice i mirisom lavande, a ručno rađene peškire šaljemo širom Srbije.' => array( 'CosyPaw towels are also %1$s — for a new baby, a christening, a first birthday or a baby shower. Every package arrives in a CosyPaw box, with a welcome note and a hint of lavender, and we ship our handmade towels all across Serbia.', 'Полотенца CosyPaw — это ещё и %1$s: на рождение малыша, крестины, первый день рождения или бэби шауэр. Каждая посылка приходит в коробке CosyPaw, с приветственной запиской и ароматом лаванды, а наши полотенца ручной работы мы отправляем по всей Сербии.' ),
	'praktičan i lep poklon'                                                                  => array( 'a practical, lovely gift', 'практичный и красивый подарок' ),
	'A kako ime CosyPaw kaže, nisu samo za decu: mnogi ih drže pored vrata kao peškir za pse, za brisanje šapa posle šetnje. Mikrofibra brzo upije vlagu i blato, a peškir se pere u mašini na 40°C.' => array( 'And as the name CosyPaw says, they are not just for children: many people keep one by the door as a dog towel, for wiping paws after a walk. Microfiber soaks up damp and mud quickly, and the towel goes in the washing machine at 40°C.', 'А как подсказывает название CosyPaw, они не только для детей: многие держат их у двери как полотенце для собаки — вытирать лапы после прогулки. Микрофибра быстро впитывает влагу и грязь, а полотенце стирается в машине при 40°C.' ),

	// Motif collection page (page-deciji-peskiri-sa-motivima-zivotinja.php).
	'Putanja'                                                                                 => array( 'Breadcrumb', 'Навигационная цепочка' ),
	'Početna'                                                                                 => array( 'Home', 'Главная' ),
	'Kolekcija'                                                                               => array( 'Collection', 'Коллекция' ),
	'Dečiji peškiri u obliku životinja'                                                    => array( 'Children’s towels in animal shapes', 'Детские полотенца в форме животных' ),
	'%1$d ručno rađenih peškira od mikrofibre, sa alkom za kačenje, od %2$s po komadu. Izaberi drugara za svoje dete — kupi jedan ili ih spoji u paket i uštedi.' => array( '%1$d handmade microfiber towels with a hanging loop, from %2$s each. Pick a friend for your child — buy one, or bundle them and save.', '%1$d полотенец ручной работы из микрофибры, с петелькой, от %2$s за штуку. Выбери друга для своего ребёнка — купи одно или собери набор и сэкономь.' ),
	'Peškiri životinjice'                                                                     => array( 'Little animal towels', 'Полотенца-зверята' ),
	'Meki drugari koji čekaju na kuki — dete samo bira svog i raduje mu se posle svakog pranja ruku.' => array( 'Soft friends waiting on the hook — a child picks their own and looks forward to it after every hand wash.', 'Мягкие друзья ждут на крючке — ребёнок сам выбирает своего и радуется ему после каждого мытья рук.' ),
	'Zalogajčići'                                                                             => array( 'Little treats', 'Вкусняшки' ),
	'Voće i poslastice od mikrofibre — vesela sitnica za kupatilo ili kuhinju.'               => array( 'Fruit and sweets in microfiber — a cheerful little thing for the bathroom or the kitchen.', 'Фрукты и сладости из микрофибры — весёлая мелочь для ванной или кухни.' ),
	'Cvetići i listići'                                                                       => array( 'Flowers and leaves', 'Цветочки и листочки' ),
	'Nežni cvetni oblici za mirniji, prirodni kutak.'                                         => array( 'Gentle floral shapes for a calmer, natural corner.', 'Нежные цветочные формы для спокойного, природного уголка.' ),
	'Peškiri u torbi'                                                                         => array( 'Towels in a bag', 'Полотенца в сумочке' ),
	'Još peškira'                                                                             => array( 'More towels', 'Ещё полотенца' ),
	'Zašto oblici'                                                                            => array( 'Why shapes', 'Зачем формы' ),
	'Dečija higijena kroz igru'                                                               => array( 'Children’s hygiene through play', 'Детская гигиена через игру' ),
	'Dečiji peškiri u obliku životinja su mnogo više od običnog tekstila za kupatilo. Kad dete vidi omiljenog drugara kako ga čeka na kuki, baš na njegovoj visini, pranje ruku prestaje da bude obaveza i postaje igra.' => array( 'Children’s towels in animal shapes are much more than ordinary bathroom textiles. When a child sees a favourite friend waiting on the hook, right at their height, washing hands stops being a chore and becomes a game.', 'Детские полотенца в форме животных — это гораздо больше, чем обычный текстиль для ванной. Когда ребёнок видит любимого друга, который ждёт его на крючке прямо на его высоте, мытьё рук перестаёт быть обязанностью и становится игрой.' ),
	'Zato je peškir za ruke zeka jedan od najomiljenijih: mekan je, prijateljski i deca ga odmah prepoznaju. Sovica podseća na mirne večernje rutine, a panda i kapibarica unose malo smeha u svako jutro.' => array( 'That is why the bunny hand towel is one of the favourites: it is soft, friendly and children recognise it at once. The little owl is a reminder of calm evening routines, while the panda and the capybara bring a little laughter to every morning.', 'Поэтому полотенце для рук «зайчик» — одно из самых любимых: мягкое, дружелюбное, и дети сразу его узнают. Совушка напоминает о спокойных вечерних ритуалах, а панда и капибара приносят немного смеха в каждое утро.' ),
	'Svi peškiri su od plišane mikrofibre, nežne prema koži, koja se brzo suši i ostaje meka i posle mnogo pranja. Alka za kačenje drži peškir uvek nadohvat ruke, pa dete samo briše ruke i gradi higijenske navike koje ostaju. Svaki je šiven ručno, pa nema dva potpuno ista — baš kao ni dece.' => array( 'Every towel is made of plush microfiber that is gentle on skin, dries quickly and stays soft wash after wash. The hanging loop keeps the towel always within reach, so the child dries their own hands and builds hygiene habits that last. Each one is sewn by hand, so no two are exactly alike — just like children.', 'Все полотенца из плюшевой микрофибры, нежной к коже, которая быстро сохнет и остаётся мягкой после многих стирок. Петелька держит полотенце всегда под рукой, поэтому ребёнок сам вытирает руки и вырабатывает гигиенические привычки, которые остаются. Каждое сшито вручную, поэтому нет двух совершенно одинаковых — как и детей.' ),
	'Kako nastaju naši %s i šta sve stiže u paketu, pročitaj na početnoj strani.'             => array( 'How our %s are made, and everything that arrives in the package, is on the home page.', 'Как создаются наши %s и что приходит в посылке — читай на главной странице.' ),
	'ručno rađeni peškiri'                                                                    => array( 'handmade towels', 'полотенца ручной работы' ),
	'Tražiš poklon za bebu ili dete? Pogledaj naše %s — spakovane u CosyPaw kutiju, spremne za predaju.' => array( 'Looking for a gift for a baby or a child? See our %s — packed in a CosyPaw box, ready to hand over.', 'Ищешь подарок для малыша или ребёнка? Посмотри наши %s — в коробке CosyPaw, готовые к вручению.' ),
	'poklon setove peškira'                                                                   => array( 'towel gift sets', 'подарочные наборы полотенец' ),

	// Gift-set page (page-poklon-setovi-za-bebu-i-decu.php).
	'Poklon setovi peškira za decu i bebe'                                                    => array( 'Towel gift sets for children and babies', 'Подарочные наборы полотенец для детей и малышей' ),
	'Pokloni'                                                                                 => array( 'Gifts', 'Подарки' ),
	'Ručno rađeni dečiji peškiri od mikrofibre, složeni u set po tvom izboru i spakovani u CosyPaw kutiju — poklon za bebu i decu koji se otvara uz osmeh i koristi svaki dan.' => array( 'Handmade children’s microfiber towels, put together into a set of your choosing and packed in a CosyPaw box — a gift for babies and children that is opened with a smile and used every day.', 'Детские полотенца ручной работы из микрофибры, собранные в набор на твой выбор и упакованные в коробку CosyPaw, — подарок для малышей и детей, который открывают с улыбкой и используют каждый день.' ),
	'Poklon za svaku priliku'                                                                 => array( 'A gift for every occasion', 'Подарок для любого случая' ),
	'Za rođenje bebe'                                                                         => array( 'For a new baby', 'На рождение малыша' ),
	'Devojčica ili dečak — mekani drugar za dečiju sobu ili kupatilo, spreman da dočeka bebu.' => array( 'A girl or a boy — a soft friend for the nursery or the bathroom, ready to welcome the baby.', 'Девочка или мальчик — мягкий друг для детской или ванной, готовый встретить малыша.' ),
	'Za krštenje'                                                                             => array( 'For a christening', 'На крестины' ),
	'Nežan poklon set za bebu koji ostaje u upotrebi i dugo posle slavlja.'                   => array( 'A gentle baby gift set that stays in use long after the celebration.', 'Нежный подарочный набор для малыша, которым пользуются ещё долго после праздника.' ),
	'Za prvi rođendan'                                                                        => array( 'For a first birthday', 'На первый день рождения' ),
	'Peškiri koje dete prepoznaje i uz koje uči da samo briše ruke.'                          => array( 'Towels a child recognises, and learns to dry their own hands with.', 'Полотенца, которые ребёнок узнаёт и с которыми учится сам вытирать руки.' ),
	'Za bebi šauer'                                                                           => array( 'For a baby shower', 'На бэби шауэр' ),
	'Poklon koji se otvara uz osmeh i koji roditelji zaista koriste svaki dan.'               => array( 'A gift opened with a smile, that parents really do use every day.', 'Подарок, который открывают с улыбкой и которым родители действительно пользуются каждый день.' ),
	'Zašto CosyPaw poklon'                                                                    => array( 'Why a CosyPaw gift', 'Почему подарок CosyPaw' ),
	'Unikatni pokloni za bebu i decu'                                                         => array( 'Unique gifts for babies and children', 'Уникальные подарки для малышей и детей' ),
	'Pronaći unikatan poklon nije lako, ali poklon set peškira za bebu to rešava jednostavno: praktičan je, lep i pamti se. Svaki set stiže u CosyPaw kutiji, sa porukom dobrodošlice i mirisom lavande, spreman da se preda bez dodatnog pakovanja.' => array( 'Finding a unique gift is not easy, but a baby towel gift set makes it simple: it is practical, lovely and remembered. Every set arrives in a CosyPaw box, with a welcome note and a hint of lavender, ready to hand over without any extra wrapping.', 'Найти уникальный подарок непросто, но подарочный набор полотенец для малыша решает это легко: он практичный, красивый и запоминается. Каждый набор приходит в коробке CosyPaw, с приветственной запиской и ароматом лаванды, готовый к вручению без дополнительной упаковки.' ),
	'Bilo da biraš poklon set za bebu za krštenje, poklon za rođenje bebe — devojčica ili dečak — ili poklone za prvi rođendan, roditelji će peškire koristiti svaki dan. Svaki put kad vide drugara na kuki, na dečijoj visini, setiće se tvog gesta. Kao poklon za baby shower naši setovi su posebno omiljeni, jer spajaju lepo i korisno.' => array( 'Whether you are choosing a baby gift set for a christening, a gift for a new baby — girl or boy — or first-birthday gifts, the parents will use the towels every day. Every time they see the friend on the hook, at a child’s height, they will remember your gesture. As a baby shower gift our sets are especially loved, because they are both lovely and useful.', 'Выбираешь ли ты подарочный набор на крестины, подарок на рождение малыша — девочки или мальчика — или подарки на первый день рождения, родители будут пользоваться полотенцами каждый день. Каждый раз, видя друга на крючке на высоте ребёнка, они вспомнят о твоём внимании. Как подарок на бэби шауэр наши наборы особенно любимы, потому что сочетают красивое и полезное.' ),
	'dečijih peškira u obliku životinja'                                                   => array( 'children’s towels in animal shapes', 'детских полотенец в форме животных' ),
	'Ako tražiš personalizovane poklone za decu, set sastavljaš sam: izaberi oblike po ukusu deteta iz naše kolekcije %s, zalogajčića i cvetića.' => array( 'If you are looking for personalised gifts for children, you put the set together yourself: choose shapes to the child’s taste from our collection of %s, treats and flowers.', 'Если ищешь персонализированные подарки для детей, набор собираешь сам: выбери формы по вкусу ребёнка из нашей коллекции %s, вкусняшек и цветочков.' ),
	'U paketu od %1$d peškira plaćaš %2$d — pokloni za decu ne moraju biti skupi da bi bili nezaboravni.' => array( 'In a package of %1$d towels you pay for %2$d — gifts for children do not have to be expensive to be unforgettable.', 'В наборе из %1$d полотенец ты платишь за %2$d — подарки для детей не обязаны быть дорогими, чтобы быть незабываемыми.' ),
	'Za porudžbine preko %s dostava je besplatna, a plaćaš pouzećem.'                         => array( 'Orders over %s ship free, and you pay cash on delivery.', 'Для заказов свыше %s доставка бесплатная, а оплата — при получении.' ),
	'Pokloni za bebu širom Srbije stižu za 2–4 radna dana. Više o tome kako nastaju CosyPaw peškiri pročitaj na %s.' => array( 'Baby gifts arrive anywhere in Serbia in 2–4 business days. Read more about how CosyPaw towels are made on the %s.', 'Подарки для малышей приходят по всей Сербии за 2–4 рабочих дня. Подробнее о том, как создаются полотенца CosyPaw, читай на %s.' ),
	'početnoj strani'                                                                         => array( 'home page', 'главной странице' ),
	'Naruči poklon set'                                                                       => array( 'Order a gift set', 'Заказать подарочный набор' ),

	// Magične krpice (inc/Cloths.php). The product names and copy are stored
	// in the database in Serbian and translated on the storefront while they
	// are left as created, so each stored string is a msgid here.
	'Magična krpica Avokado, limeta'                                                          => array( 'Avocado magic cloth, lime', 'Волшебная салфетка «Авокадо», лайм' ),
	'Magična krpica Avokado, žalfija'                                                         => array( 'Avocado magic cloth, sage', 'Волшебная салфетка «Авокадо», шалфей' ),
	'Magična krpa od mikrofibera u jarkoj limeta zelenoj boji, sa vezenim avokadom. Upija vodu, hvata prašinu i briše staklo bez tragova — za kuhinju, kupatilo i ogledala.' => array( 'A microfiber magic cloth in bright lime green, with an embroidered avocado. It soaks up water, traps dust and wipes glass without streaks — for the kitchen, the bathroom and mirrors.', 'Волшебная салфетка из микрофибры ярко-лаймового цвета с вышитым авокадо. Впитывает воду, собирает пыль и вытирает стекло без разводов — для кухни, ванной и зеркал.' ),
	'Magična krpa od mikrofibera u nežnoj žalfija zelenoj boji, sa vezenim nasmejanim avokadom. Upija vodu, hvata prašinu i briše staklo bez tragova — za kuhinju, kupatilo i ogledala.' => array( 'A microfiber magic cloth in soft sage green, with an embroidered smiling avocado. It soaks up water, traps dust and wipes glass without streaks — for the kitchen, the bathroom and mirrors.', 'Волшебная салфетка из микрофибры нежного шалфейно-зелёного цвета с вышитым улыбающимся авокадо. Впитывает воду, собирает пыль и вытирает стекло без разводов — для кухни, ванной и зеркал.' ),
	'Rebrasta mikrofibra upija vodu i hvata prašinu umesto da je razmazuje, pa je ista krpa jednako dobra kao kuhinjska krpa za radne površine i sudove i kao krpa za staklo, ogledala i tuš kabine. Briše bez tragova i dlačica, bez agresivnih sredstava. Višekratna je i pere se u mašini na 40°C, bez omekšivača, da zadrži upijanje — i zamenjuje gomilu papirnih ubrusa.' => array( 'Ribbed microfiber soaks up water and traps dust instead of smearing it, so the same cloth is just as good as a kitchen cloth for worktops and dishes as it is as a glass cloth for windows, mirrors and shower screens. It wipes without streaks or lint, and without harsh cleaners. It is reusable and machine-washable at 40°C, without fabric softener so it keeps absorbing — and it replaces a pile of paper towels.', 'Рельефная микрофибра впитывает воду и собирает пыль, а не размазывает её, поэтому одна и та же салфетка одинаково хороша и как кухонная — для столешниц и посуды, — и как салфетка для стекла, зеркал и душевых кабин. Вытирает без разводов и ворса, без агрессивных средств. Многоразовая, стирается в машине при 40°C без кондиционера, чтобы сохранить впитываемость, — и заменяет гору бумажных полотенец.' ),
	'Namena'                                                                                  => array( 'Use', 'Назначение' ),
	'Rebrasta mikrofibra — upija vodu i hvata prašinu.'                                       => array( 'Ribbed microfiber — soaks up water and traps dust.', 'Рельефная микрофибра — впитывает воду и собирает пыль.' ),
	'Kuhinja, staklo, ogledala, tuš kabine i radne površine.'                                 => array( 'Kitchen, glass, mirrors, shower screens and worktops.', 'Кухня, стекло, зеркала, душевые кабины и столешницы.' ),
	'Mašinsko pranje na 40°C, bez omekšivača.'                                                => array( 'Machine wash at 40°C, without fabric softener.', 'Машинная стирка при 40°C, без кондиционера.' ),
	'Dodaj %s u korpu'                                                                        => array( 'Add %s to cart', 'Добавить «%s» в корзину' ),
	'Novo'                                                                                    => array( 'New', 'Новинка' ),
	'Magične krpice'                                                                          => array( 'Magic cloths', 'Волшебные салфетки' ),
	'Mikrofiber krpice sa vezenim avokadom — upijaju vodu, hvataju prašinu i brišu staklo bez tragova. %s po komadu, van paketa peškira.' => array( 'Microfiber cloths with an embroidered avocado — they soak up water, trap dust and wipe glass without streaks. %s each, outside the towel packages.', 'Салфетки из микрофибры с вышитым авокадо — впитывают воду, собирают пыль и вытирают стекло без разводов. %s за штуку, вне наборов полотенец.' ),
	'Sve o magičnim krpama'                                                                   => array( 'All about the magic cloths', 'Всё о волшебных салфетках' ),
	'Set od 2 magične krpice Avokado'                                                         => array( 'Set of 2 Avocado magic cloths', 'Набор из 2 волшебных салфеток «Авокадо»' ),
	'Obe magične krpice Avokado u jednom setu — limeta i žalfija zelena. Upijaju vodu, hvataju prašinu i brišu staklo bez tragova: jedna za kuhinju, druga za staklo i ogledala.' => array( 'Both Avocado magic cloths in one set — lime and sage green. They soak up water, trap dust and wipe glass without streaks: one for the kitchen, the other for glass and mirrors.', 'Обе волшебные салфетки «Авокадо» в одном наборе — лайм и шалфейно-зелёная. Впитывают воду, собирают пыль и вытирают стекло без разводов: одна для кухни, другая для стекла и зеркал.' ),
	'U setu'                                                                                  => array( 'In the set', 'В наборе' ),
	'2 krpice: limeta i žalfija zelena.'                                                      => array( '2 cloths: lime and sage green.', '2 салфетки: лайм и шалфейно-зелёная.' ),
	'Uštedi %s'                                                                               => array( 'Save %s', 'Экономия %s' ),
	'Pojedinačno:'                                                                            => array( 'Separately:', 'По отдельности:' ),
	'Dodaj set u korpu'                                                                       => array( 'Add the set to cart', 'Добавить набор в корзину' ),

	// Magične krpe page (page-magicne-krpe.php).
	'Magične krpe'                                                                            => array( 'Magic cloths', 'Волшебные салфетки' ),
	'Magična krpa za savršeno čist dom'                                                       => array( 'A magic cloth for a spotless home', 'Волшебная салфетка для идеально чистого дома' ),
	'Mikrofiber krpa sa vezenim avokadom koja upija vodu, hvata prašinu i briše staklo bez tragova. Kuhinjska krpa i krpa za staklo u jednoj, za %s.' => array( 'A microfiber cloth with an embroidered avocado that soaks up water, traps dust and wipes glass without streaks. A kitchen cloth and a glass cloth in one, for %s.', 'Салфетка из микрофибры с вышитым авокадо, которая впитывает воду, собирает пыль и вытирает стекло без разводов. Кухонная салфетка и салфетка для стекла в одной — за %s.' ),
	'Čišćenje bez tragova'                                                                    => array( 'Streak-free cleaning', 'Уборка без разводов' ),
	'Kuhinjske krpe i krpe za staklo u jednoj'                                                => array( 'Kitchen cloths and glass cloths in one', 'Кухонные салфетки и салфетки для стекла в одной' ),
	'Želiš li da čišćenje bude brže, lakše i potpuno bez tragova? Magična krpa je upravo to. Ova mikrofiber krpa spaja mekoću, izdržljivost i praktičnost: kao kuhinjska krpa upija vodu sa radnih površina i sudova, a kao krpa za prašinu zadržava čestice umesto da ih razbacuje po vazduhu.' => array( 'Have you ever wished cleaning were quicker, easier and completely streak-free? The magic cloth is exactly that. This microfiber cloth combines softness, durability and practicality: as a kitchen cloth it soaks up water from worktops and dishes, and as a dust cloth it holds on to particles instead of scattering them into the air.', 'Хотелось ли тебе, чтобы уборка была быстрее, проще и совсем без разводов? Волшебная салфетка — именно это. Эта салфетка из микрофибры сочетает мягкость, прочность и практичность: как кухонная она впитывает воду со столешниц и посуды, а как салфетка для пыли удерживает частицы, а не разбрасывает их в воздух.' ),
	'Posebno je dobra na staklu. Magične krpe za stakla ostavljaju blistav sjaj bez mrlja, tragova i dlačica, bez agresivnih hemijskih sredstava. Nisu samo za prozore: ista krpa za staklo briše ogledala u kupatilu, staklene vitrine, tuš kabine, pa i ekran televizora.' => array( 'It is especially good on glass. Magic glass cloths leave a brilliant shine with no smudges, streaks or lint, and no harsh chemicals. They are not only for windows: the same glass cloth wipes bathroom mirrors, glass cabinets, shower screens and even a TV screen.', 'Особенно хороша на стекле. Волшебные салфетки для стекла оставляют яркий блеск без пятен, разводов и ворса, без агрессивной химии. Они не только для окон: та же салфетка для стекла протирает зеркала в ванной, стеклянные витрины, душевые кабины и даже экран телевизора.' ),
	'Ako tražiš najbolju krpu za staklo, onu kakvu očekuješ od profesionalnih krpa za stakla, a da ostane i u kuhinji, ovo je ta. Krpe su višekratne, peru se u mašini na 40°C i zamenjuju gomilu papirnih ubrusa.' => array( 'If you are looking for the best glass cloth — the kind you expect from professional glass cloths, that also earns its place in the kitchen — this is it. The cloths are reusable, machine-washable at 40°C and replace a pile of paper towels.', 'Если ищешь лучшую салфетку для стекла — такую, какой ждёшь от профессиональных салфеток для стёкол, и чтобы она пригодилась и на кухне, — это она. Салфетки многоразовые, стираются в машине при 40°C и заменяют гору бумажных полотенец.' ),
	'Magične krpice šaljemo kao i %s peškire — u CosyPaw paketu, uz plaćanje pouzećem i dostavu 2–4 dana širom Srbije.' => array( 'We send the magic cloths just like %s towels — in a CosyPaw package, cash on delivery, delivered across Serbia in 2–4 days.', 'Волшебные салфетки мы отправляем так же, как полотенца %s, — в посылке CosyPaw, с оплатой при получении и доставкой по всей Сербии за 2–4 дня.' ),
	'Dodaj ih uz paket peškira i lakše stigni do besplatne dostave preko %s.'                 => array( 'Add them to a towel package and reach free delivery over %s more easily.', 'Добавь их к набору полотенец — и легче дойдёшь до бесплатной доставки от %s.' ),
	'Poruči magične krpe'                                                                     => array( 'Order magic cloths', 'Заказать волшебные салфетки' ),

	// Cuddle Puff jastuci (inc/Pillows.php) and dugi peškiri
	// (inc/LongTowels.php). Like the cloths, names, colours and copy are
	// stored in Serbian and translated on the storefront while left as created.
	'Cuddle Puff jastuk Kružić, braon'                                                        => array( 'Cuddle Puff round pillow, brown', 'Подушка Cuddle Puff «Кружок», коричневая' ),
	'Cuddle Puff jastuk Kružić, sivi'                                                         => array( 'Cuddle Puff round pillow, grey', 'Подушка Cuddle Puff «Кружок», серая' ),
	'Cuddle Puff jastuk Kružić, žuti'                                                         => array( 'Cuddle Puff round pillow, yellow', 'Подушка Cuddle Puff «Кружок», жёлтая' ),
	'Cuddle Puff jastuk Kružić, zeleni'                                                       => array( 'Cuddle Puff round pillow, green', 'Подушка Cuddle Puff «Кружок», зелёная' ),
	'Cuddle Puff jastuk Kružić, roze'                                                         => array( 'Cuddle Puff round pillow, pink', 'Подушка Cuddle Puff «Кружок», розовая' ),
	'Cuddle Puff jastuk Kockasti, roze'                                                       => array( 'Cuddle Puff square pillow, pink', 'Подушка Cuddle Puff «Квадрат», розовая' ),
	'Cuddle Puff jastuk Kockasti, braon'                                                      => array( 'Cuddle Puff square pillow, brown', 'Подушка Cuddle Puff «Квадрат», коричневая' ),
	'Cuddle Puff jastuk Kockasti, sivi'                                                       => array( 'Cuddle Puff square pillow, grey', 'Подушка Cuddle Puff «Квадрат», серая' ),
	'Mekani plišani jastuk u obliku kružića, u toploj braon boji, sa vezenim okicama, kljunićem i zelenim listićem. Za stolicu, fotelju, sofu ili dečiju sobu.' => array( 'A soft, round plush pillow in warm brown, with embroidered eyes, a little beak and a green leaf. For a chair, an armchair, the sofa or a child’s room.', 'Мягкая плюшевая подушка-кружок тёплого коричневого цвета, с вышитыми глазками, клювиком и зелёным листиком. Для стула, кресла, дивана или детской.' ),
	'Mekani plišani jastuk u obliku kružića, u nežnoj sivoj boji, sa vezenim okicama, kljunićem i zelenim listićem. Za stolicu, fotelju, sofu ili dečiju sobu.' => array( 'A soft, round plush pillow in gentle grey, with embroidered eyes, a little beak and a green leaf. For a chair, an armchair, the sofa or a child’s room.', 'Мягкая плюшевая подушка-кружок нежного серого цвета, с вышитыми глазками, клювиком и зелёным листиком. Для стула, кресла, дивана или детской.' ),
	'Mekani plišani jastuk u obliku kružića, u veseloj žutoj boji, sa vezenim okicama, kljunićem i zelenim listićem. Za stolicu, fotelju, sofu ili dečiju sobu.' => array( 'A soft, round plush pillow in cheerful yellow, with embroidered eyes, a little beak and a green leaf. For a chair, an armchair, the sofa or a child’s room.', 'Мягкая плюшевая подушка-кружок весёлого жёлтого цвета, с вышитыми глазками, клювиком и зелёным листиком. Для стула, кресла, дивана или детской.' ),
	'Mekani plišani jastuk u obliku kružića, u svetloj zelenoj boji, sa vezenim okicama, kljunićem i listićem. Za stolicu, fotelju, sofu ili dečiju sobu.' => array( 'A soft, round plush pillow in light green, with embroidered eyes, a little beak and a leaf. For a chair, an armchair, the sofa or a child’s room.', 'Мягкая плюшевая подушка-кружок светло-зелёного цвета, с вышитыми глазками, клювиком и листиком. Для стула, кресла, дивана или детской.' ),
	'Mekani plišani jastuk u obliku kružića, u nežnoj roze boji, sa vezenim okicama, kljunićem i zelenim listićem. Za stolicu, fotelju, sofu ili dečiju sobu.' => array( 'A soft, round plush pillow in gentle pink, with embroidered eyes, a little beak and a green leaf. For a chair, an armchair, the sofa or a child’s room.', 'Мягкая плюшевая подушка-кружок нежного розового цвета, с вышитыми глазками, клювиком и зелёным листиком. Для стула, кресла, дивана или детской.' ),
	'Mekani plišani kockasti jastuk sa talasastim ivicama, u roze boji, sa vezenim okicama, kljunićem i zelenim listićem. Jastuk za stolicu, fotelju ili sofu.' => array( 'A soft, square plush pillow with wavy edges in pink, with embroidered eyes, a little beak and a green leaf. A pillow for a chair, an armchair or the sofa.', 'Мягкая квадратная плюшевая подушка с волнистыми краями, розовая, с вышитыми глазками, клювиком и зелёным листиком. Подушка для стула, кресла или дивана.' ),
	'Mekani plišani kockasti jastuk sa talasastim ivicama, u svetloj braon boji, sa vezenim okicama, kljunićem i zelenim listićem. Jastuk za stolicu, fotelju ili sofu.' => array( 'A soft, square plush pillow with wavy edges in light brown, with embroidered eyes, a little beak and a green leaf. A pillow for a chair, an armchair or the sofa.', 'Мягкая квадратная плюшевая подушка с волнистыми краями, светло-коричневая, с вышитыми глазками, клювиком и зелёным листиком. Подушка для стула, кресла или дивана.' ),
	'Mekani plišani kockasti jastuk sa talasastim ivicama, u sivoj boji, sa vezenim okicama, kljunićem i zelenim listićem. Jastuk za stolicu, fotelju ili sofu.' => array( 'A soft, square plush pillow with wavy edges in grey, with embroidered eyes, a little beak and a green leaf. A pillow for a chair, an armchair or the sofa.', 'Мягкая квадратная плюшевая подушка с волнистыми краями, серая, с вышитыми глазками, клювиком и зелёным листиком. Подушка для стула, кресла или дивана.' ),
	'Cuddle Puff jastuci su punašni, mekani jastuci od rebraste plišane tkanine, sa vezenim okicama, žutim kljunićem i plišanim zelenim listićem na vrhu. Talasaste ivice obrubljene su svetlom, čupavom tkaninom, a sa strane je omčica za kačenje. Jednako lepo stoje kao jastuk za stolicu ili fotelju, kao ukrasni jastuk na sofi i kao plišani drug u dečijoj sobi — i unose osmeh u svaki kutak doma.' => array( 'Cuddle Puff pillows are plump, soft pillows of ribbed plush, with embroidered eyes, a yellow beak and a plush green leaf on top. Their wavy edges are trimmed with a light, fluffy fabric, and there is a little hanging loop on the side. They look just as good as a chair or armchair cushion, as a decorative pillow on the sofa and as a plush friend in a child’s room — and they bring a smile to every corner of the home.', 'Подушки Cuddle Puff — пухлые мягкие подушки из рельефного плюша, с вышитыми глазками, жёлтым клювиком и плюшевым зелёным листиком сверху. Волнистые края отделаны светлой пушистой тканью, а сбоку есть петелька для подвешивания. Они одинаково хороши как подушка на стул или кресло, как декоративная подушка на диване и как плюшевый друг в детской — и приносят улыбку в каждый уголок дома.' ),
	'Braon'                                                                                   => array( 'Brown', 'Коричневый' ),
	'Siva'                                                                                    => array( 'Grey', 'Серый' ),
	'Žuta'                                                                                    => array( 'Yellow', 'Жёлтый' ),
	'Zelena'                                                                                  => array( 'Green', 'Зелёный' ),
	'Roze'                                                                                    => array( 'Pink', 'Розовый' ),
	'Krem'                                                                                    => array( 'Cream', 'Кремовый' ),
	'Plava'                                                                                   => array( 'Blue', 'Голубой' ),
	'Bež'                                                                                     => array( 'Beige', 'Бежевый' ),
	'Oblik'                                                                                   => array( 'Shape', 'Форма' ),
	'Kružić, okrugao sa talasastim ivicama.'                                                  => array( 'Round, with wavy edges.', 'Кружок — круглая, с волнистыми краями.' ),
	'Kockasti, sa talasastim ivicama.'                                                        => array( 'Square, with wavy edges.', 'Квадрат — с волнистыми краями.' ),
	'Boja'                                                                                    => array( 'Colour', 'Цвет' ),
	'Detalji'                                                                                 => array( 'Details', 'Детали' ),
	'Vezene okice i kljunić, plišani listić, omčica za kačenje.'                              => array( 'Embroidered eyes and beak, a plush leaf, a hanging loop.', 'Вышитые глазки и клювик, плюшевый листик, петелька для подвешивания.' ),
	'Stolica, fotelja, sofa i dečija soba.'                                                   => array( 'Chair, armchair, sofa and children’s room.', 'Стул, кресло, диван и детская.' ),
	'Dugi peškir Meda, krem'                                                                  => array( 'Teddy long towel, cream', 'Длинное полотенце «Мишка», кремовое' ),
	'Dugi peškir Meda, roze'                                                                  => array( 'Teddy long towel, pink', 'Длинное полотенце «Мишка», розовое' ),
	'Dugi peškir Meda, plavi'                                                                 => array( 'Teddy long towel, blue', 'Длинное полотенце «Мишка», голубое' ),
	'Dugi peškir Meda, bež'                                                                   => array( 'Teddy long towel, beige', 'Длинное полотенце «Мишка», бежевое' ),
	'Mekani dugi peškir u krem boji, sa vezenim medom, šapicom i natpisom GOODLUCK na borduri. Za lice, ruke i kosu.' => array( 'A soft long towel in cream, with an embroidered teddy, a paw and the word GOODLUCK on its border. For face, hands and hair.', 'Мягкое длинное полотенце кремового цвета с вышитым мишкой, лапкой и надписью GOODLUCK на бордюре. Для лица, рук и волос.' ),
	'Mekani dugi peškir u nežnoj roze boji, sa vezenim medom, šapicom i natpisom GOODLUCK na borduri. Za lice, ruke i kosu.' => array( 'A soft long towel in gentle pink, with an embroidered teddy, a paw and the word GOODLUCK on its border. For face, hands and hair.', 'Мягкое длинное полотенце нежного розового цвета с вышитым мишкой, лапкой и надписью GOODLUCK на бордюре. Для лица, рук и волос.' ),
	'Mekani dugi peškir u nežnoj plavoj boji, sa vezenim medom, šapicom i natpisom GOODLUCK na borduri. Za lice, ruke i kosu.' => array( 'A soft long towel in gentle blue, with an embroidered teddy, a paw and the word GOODLUCK on its border. For face, hands and hair.', 'Мягкое длинное полотенце нежного голубого цвета с вышитым мишкой, лапкой и надписью GOODLUCK на бордюре. Для лица, рук и волос.' ),
	'Mekani dugi peškir u toploj bež boji, sa vezenim medom, šapicom i natpisom GOODLUCK na borduri. Za lice, ruke i kosu.' => array( 'A soft long towel in warm beige, with an embroidered teddy, a paw and the word GOODLUCK on its border. For face, hands and hair.', 'Мягкое длинное полотенце тёплого бежевого цвета с вышитым мишкой, лапкой и надписью GOODLUCK на бордюре. Для лица, рук и волос.' ),
	'Dugi peškir od mekane, upijajuće tkanine, sa vezenim medom, šapicom i natpisom GOODLUCK na tkanoj borduri. Duži je od peškirića za ruke, pa je zgodan i za lice i kosu — na držaču pored lavaboa ili prebačen preko merdevina za peškire. Nežne boje lako se uklope u svako kupatilo, a stiže kao i ostali CosyPaw proizvodi: uz plaćanje pouzećem i dostavu širom Srbije.' => array( 'A long towel of soft, absorbent fabric, with an embroidered teddy, a paw and the word GOODLUCK on its woven border. It is longer than a hand towel, so it works for face and hair too — on the rail by the basin or draped over a towel ladder. Its soft colours suit any bathroom, and it arrives like every CosyPaw product: cash on delivery, shipped anywhere in Serbia.', 'Длинное полотенце из мягкой впитывающей ткани с вышитым мишкой, лапкой и надписью GOODLUCK на тканом бордюре. Оно длиннее полотенца для рук, поэтому подходит и для лица, и для волос — на держателе у раковины или на лестнице для полотенец. Нежные цвета легко впишутся в любую ванную, а приходит оно, как и все товары CosyPaw: с оплатой при получении и доставкой по всей Сербии.' ),
	'Vezeni meda, šapica i natpis GOODLUCK.'                                                  => array( 'Embroidered teddy, paw and the word GOODLUCK.', 'Вышитый мишка, лапка и надпись GOODLUCK.' ),
	'Lice, ruke i kosa — za celu porodicu.'                                                   => array( 'Face, hands and hair — for the whole family.', 'Лицо, руки и волосы — для всей семьи.' ),
	'Izaberi boju'                                                                            => array( 'Choose a colour', 'Выбери цвет' ),

	// Front page sections and the header link for them.
	'Za kupatilo'                                                                             => array( 'For the bathroom', 'Для ванной' ),
	'Dugi peškiri'                                                                            => array( 'Long towels', 'Длинные полотенца' ),
	'Mekani dugi peškiri sa vezenim medom i šapicom, za lice, ruke i kosu. %s po komadu, van paketa peškirića.' => array( 'Soft long towels with an embroidered teddy and paw, for face, hands and hair. %s each, outside the towel packages.', 'Мягкие длинные полотенца с вышитым мишкой и лапкой — для лица, рук и волос. %s за штуку, вне наборов полотенчиков.' ),
	'Sve o dugim peškirima'                                                                   => array( 'All about the long towels', 'Всё о длинных полотенцах' ),
	'Cuddle Puff jastuci'                                                                     => array( 'Cuddle Puff pillows', 'Подушки Cuddle Puff' ),
	'Punašni plišani jastuci sa vezenim okicama i listićem, u obliku kružića ili kockasti. Za stolicu, fotelju, sofu ili dečiju sobu — %s po komadu.' => array( 'Plump plush pillows with embroidered eyes and a leaf, round or square. For a chair, an armchair, the sofa or a child’s room — %s each.', 'Пухлые плюшевые подушки с вышитыми глазками и листиком, круглые или квадратные. Для стула, кресла, дивана или детской — %s за штуку.' ),
	'Sve o Cuddle Puff jastucima'                                                             => array( 'All about the Cuddle Puff pillows', 'Всё о подушках Cuddle Puff' ),
	'Jastuci'                                                                                 => array( 'Pillows', 'Подушки' ),

	// Dugi peškiri page (page-dugi-peskiri.php).
	'Dugi peškiri sa vezenim medom'                                                           => array( 'Long towels with an embroidered teddy', 'Длинные полотенца с вышитым мишкой' ),
	'Mekani, upijajući peškiri za lice, ruke i kosu, sa vezenim medom i šapicom na borduri. Nežne boje za svako kupatilo, za %s po komadu.' => array( 'Soft, absorbent towels for face, hands and hair, with an embroidered teddy and paw on the border. Soft colours for any bathroom, at %s each.', 'Мягкие впитывающие полотенца для лица, рук и волос, с вышитым мишкой и лапкой на бордюре. Нежные цвета для любой ванной — %s за штуку.' ),
	'Peškiri za kupatilo'                                                                     => array( 'Bathroom towels', 'Полотенца для ванной' ),
	'Mekani peškiri za lice, ruke i kosu'                                                     => array( 'Soft towels for face, hands and hair', 'Мягкие полотенца для лица, рук и волос' ),
	'Dugi peškir je onaj koji uzmeš svakog jutra: za lice posle umivanja, za ruke pored lavaboa i za kosu posle tuširanja. Duži je od peškirića za ruke, pa jednim potezom obrišeš i lice i kosu, a mekana, upijajuća tkanina brzo pokupi vodu.' => array( 'A long towel is the one you reach for every morning: for your face after washing, your hands at the basin and your hair after a shower. It is longer than a hand towel, so one go dries both face and hair, and the soft, absorbent fabric takes up water quickly.', 'Длинное полотенце — то, за которым тянешься каждое утро: для лица после умывания, для рук у раковины и для волос после душа. Оно длиннее полотенца для рук, поэтому одним движением вытираешь и лицо, и волосы, а мягкая впитывающая ткань быстро забирает воду.' ),
	'Na tkanoj borduri izvezen je mali meda sa šapicom i natpis GOODLUCK — sitan detalj koji kupatilo čini toplijim. Krem, roze, plava i bež su nežne boje koje se lako uklope uz pločice, mermer ili drvo, a lepo stoje i kada ih kombinuješ.' => array( 'A little teddy with a paw and the word GOODLUCK are embroidered on the woven border — a small detail that makes a bathroom feel warmer. Cream, pink, blue and beige are soft colours that sit easily with tiles, marble or wood, and look good mixed together too.', 'На тканом бордюре вышиты маленький мишка с лапкой и надпись GOODLUCK — мелкая деталь, от которой ванная становится уютнее. Кремовый, розовый, голубой и бежевый — нежные цвета, которые легко сочетаются с плиткой, мрамором или деревом и красиво смотрятся вместе.' ),
	'Ako tražiš peškir za lice ili peškir za ruke koji lepo izgleda i na držaču i na merdevinama za peškire, ovo je taj. A za najmlađe imamo i %s — ručno rađene peškiriće sa alkom za kačenje.' => array( 'If you are looking for a face towel or hand towel that looks good on the rail and on a towel ladder alike, this is it. And for the little ones we also have %s — handmade towels with a hanging loop.', 'Если ищешь полотенце для лица или для рук, которое красиво смотрится и на держателе, и на лестнице для полотенец, — это оно. А для самых маленьких у нас есть %s — полотенчики ручной работы с петелькой.' ),
	'Ako tražiš peškir za lice ili peškir za ruke koji lepo izgleda i na držaču i na merdevinama za peškire, ovo je taj.' => array( 'If you are looking for a face towel or hand towel that looks good on the rail and on a towel ladder alike, this is it.', 'Если ищешь полотенце для лица или для рук, которое красиво смотрится и на держателе, и на лестнице для полотенец, — это оно.' ),
	'dečije peškire u obliku životinja'                                                    => array( 'children’s towels in animal shapes', 'детские полотенца в форме животных' ),
	'Dugi peškiri stižu kao i ostali CosyPaw proizvodi — uz plaćanje pouzećem i dostavu 2–4 dana širom Srbije.' => array( 'The long towels arrive like every CosyPaw product — cash on delivery, delivered across Serbia in 2–4 days.', 'Длинные полотенца приходят, как и все товары CosyPaw, — с оплатой при получении и доставкой по всей Сербии за 2–4 дня.' ),
	'Dodaj ih uz paket peškirića i lakše stigni do besplatne dostave preko %s.'               => array( 'Add them to a towel package and reach free delivery over %s more easily.', 'Добавь их к набору полотенчиков — и легче получить бесплатную доставку от %s.' ),
	'Poruči dugi peškir'                                                                      => array( 'Order a long towel', 'Заказать длинное полотенце' ),

	// Cuddle Puff jastuci page (page-cuddle-puff-jastuci.php).
	'Mekani Cuddle Puff jastuci sa okicama'                                                   => array( 'Soft Cuddle Puff pillows with little eyes', 'Мягкие подушки Cuddle Puff с глазками' ),
	'Punašni plišani jastuci sa vezenim okicama, kljunićem i listićem, u obliku kružića ili kockasti. Za stolicu, fotelju, sofu ili dečiju sobu, za %s.' => array( 'Plump plush pillows with embroidered eyes, a little beak and a leaf, round or square. For a chair, an armchair, the sofa or a child’s room, at %s.', 'Пухлые плюшевые подушки с вышитыми глазками, клювиком и листиком, круглые или квадратные. Для стула, кресла, дивана или детской — за %s.' ),
	'Jastuci za dom'                                                                          => array( 'Pillows for the home', 'Подушки для дома' ),
	'Jastuk za stolicu, ukrasni jastuk i plišani drug u jednom'                               => array( 'A chair cushion, a decorative pillow and a plush friend in one', 'Подушка на стул, декоративная подушка и плюшевый друг в одном' ),
	'Cuddle Puff jastuk je onaj koji svi požele da zagrle. Rebrasta plišana tkanina mekana je na dodir, a punašni, prošiveni oblik čini ga udobnim kao jastuk za stolicu ili fotelju — u kuhinji, za radnim stolom ili u dnevnoj sobi.' => array( 'A Cuddle Puff pillow is the one everyone wants to hug. The ribbed plush is soft to the touch, and the plump, quilted shape makes it a comfortable chair or armchair cushion — in the kitchen, at the desk or in the living room.', 'Подушку Cuddle Puff хочется обнять всем. Рельефный плюш мягкий на ощупь, а пухлая стёганая форма делает её удобной подушкой на стул или кресло — на кухне, за рабочим столом или в гостиной.' ),
	'Biraš između dva oblika. Kružić ima talasaste ivice kao cvet, a Kockasti je četvrtast, sa istim talasastim obrubom. Oba imaju vezene okice, žuti kljunić, zeleni plišani listić i omčicu za kačenje, i dolaze u nežnim bojama — od roze i žute do sive i braon.' => array( 'You choose between two shapes. The round one has wavy edges like a flower, and the square one has the same wavy trim. Both have embroidered eyes, a yellow beak, a green plush leaf and a hanging loop, and come in soft colours — from pink and yellow to grey and brown.', 'Можно выбрать одну из двух форм. «Кружок» с волнистыми краями, как цветок, а «Квадрат» — квадратный, с той же волнистой окантовкой. У обеих вышитые глазки, жёлтый клювик, зелёный плюшевый листик и петелька для подвешивания, а цвета нежные — от розового и жёлтого до серого и коричневого.' ),
	'Kao ukrasni jastuk na sofi ili krevetu unose boju i osmeh, a u dečijoj sobi postaju plišani drug za maženje, čitanje i igru. Lepi su i kao poklon — za useljenje, rođendan ili bez povoda.' => array( 'As a decorative pillow on the sofa or bed they bring colour and a smile, and in a child’s room they become a plush friend for cuddles, reading and play. They make a lovely gift too — for a housewarming, a birthday or no reason at all.', 'Как декоративная подушка на диване или кровати они добавляют цвета и улыбок, а в детской становятся плюшевым другом для обнимашек, чтения и игр. Это и милый подарок — на новоселье, день рождения или просто так.' ),
	'Cuddle Puff jastuke šaljemo kao i ostale CosyPaw proizvode — uz plaćanje pouzećem i dostavu 2–4 dana širom Srbije. Uz jastuk lepo idu i naši mekani proizvodi za kupatilo, poput %s.' => array( 'We send the Cuddle Puff pillows like every CosyPaw product — cash on delivery, delivered across Serbia in 2–4 days. They pair nicely with our soft bathroom pieces too, such as %s.', 'Подушки Cuddle Puff мы отправляем, как и все товары CosyPaw, — с оплатой при получении и доставкой по всей Сербии за 2–4 дня. К подушке хорошо подойдут и наши мягкие вещи для ванной, например %s.' ),
	'Cuddle Puff jastuke šaljemo kao i ostale CosyPaw proizvode — uz plaćanje pouzećem i dostavu 2–4 dana širom Srbije.' => array( 'We send the Cuddle Puff pillows like every CosyPaw product — cash on delivery, delivered across Serbia in 2–4 days.', 'Подушки Cuddle Puff мы отправляем, как и все товары CosyPaw, — с оплатой при получении и доставкой по всей Сербии за 2–4 дня.' ),
	'dugih peškira sa vezenim medom'                                                          => array( 'the long towels with an embroidered teddy', 'длинные полотенца с вышитым мишкой' ),
	'Dodaj jastuk uz paket peškirića i lakše stigni do besplatne dostave preko %s.'           => array( 'Add a pillow to a towel package and reach free delivery over %s more easily.', 'Добавь подушку к набору полотенчиков — и легче получить бесплатную доставку от %s.' ),
	'Poruči Cuddle Puff jastuk'                                                               => array( 'Order a Cuddle Puff pillow', 'Заказать подушку Cuddle Puff' ),
	'Glavna navigacija'                                                                       => array( 'Main navigation', 'Основная навигация' ),
	'Pređi na sadržaj'                                                                        => array( 'Skip to content', 'Перейти к содержимому' ),
	'Meni'                                                                                    => array( 'Menu', 'Меню' ),
	'Pauziraj najave'                                                                         => array( 'Pause the announcements', 'Приостановить объявления' ),
	'Pusti najave'                                                                            => array( 'Play the announcements', 'Возобновить объявления' ),
	'Pauziraj smenjivanje peškirića'                                                          => array( 'Pause the design slideshow', 'Приостановить смену мотивов' ),
	'Pusti smenjivanje peškirića'                                                             => array( 'Play the design slideshow', 'Возобновить смену мотивов' ),
	'Peškirići'                                                                               => array( 'Designs', 'Мотивы' ),
	'Pogledaj korpu'                                                                          => array( 'View cart', 'Посмотреть корзину' ),
	'Otvori korpu'                                                                            => array( 'Open cart', 'Открыть корзину' ),
	'dodat u korpu'                                                                           => array( 'added to cart', 'добавлено в корзину' ),
	'Demo prodavnice — porudžbina nije aktivna'                                               => array( 'Demo shop — ordering is not active', 'Демо-магазин — заказ недоступен' ),
	'Ukloni'                                                                                  => array( 'Remove', 'Удалить' ),
	'RSD'                                                                                     => array( 'RSD', 'RSD' ),
	'Žirafa'                                                                                  => array( 'Giraffe', 'Жираф' ),
	'Koala'                                                                                   => array( 'Koala', 'Коала' ),
	'Pingvin'                                                                                 => array( 'Penguin', 'Пингвин' ),
	'Sova'                                                                                    => array( 'Owl', 'Сова' ),
	'Panda'                                                                                   => array( 'Panda', 'Панда' ),
	'Meda'                                                                                    => array( 'Teddy bear', 'Мишка' ),
	'Kapibara'                                                                                => array( 'Capybara', 'Капибара' ),
	'Maca'                                                                                    => array( 'Kitty', 'Кошечка' ),
	'Kucence'                                                                                 => array( 'Puppy', 'Щенок' ),
	'Zeka'                                                                                    => array( 'Bunny', 'Зайчик' ),
	'Avokado'                                                                                 => array( 'Avocado', 'Авокадо' ),
	'Ananas'                                                                                  => array( 'Pineapple', 'Ананас' ),
	'Trešnja'                                                                                 => array( 'Cherry', 'Вишня' ),
	'Sir'                                                                                     => array( 'Cheese', 'Сыр' ),
	'Krofna'                                                                                  => array( 'Donut', 'Пончик' ),
	'Biskvit'                                                                                 => array( 'Biscuit', 'Бисквит' ),
	'Čokoladni keks'                                                                          => array( 'Chocolate cookie', 'Шоколадное печенье' ),
	'Tost'                                                                                    => array( 'Toast', 'Тост' ),
	'Lala'                                                                                    => array( 'Tulip', 'Тюльпан' ),
	'Javorov list'                                                                            => array( 'Maple leaf', 'Кленовый лист' ),
	'Single'                                                                                  => array( 'Single', 'Поштучно' ),
	'Jedan omiljeni peškirić'                                                                 => array( 'One favorite design', 'Один любимый мотив' ),
	'2+1 paket'                                                                               => array( '2+1 package', 'Набор 2+1' ),
	'Najpopularnije'                                                                          => array( 'Most popular', 'Самое популярное' ),
	'Tri peškirića po izboru'                                                                 => array( 'Three designs of your choice', 'Три мотива на выбор' ),
	'Žurnal'                                                                                  => array( 'Journal', 'Журнал' ),
	'Pročitaj više'                                                                           => array( 'Read more', 'Читать далее' ),
	'Strane:'                                                                                 => array( 'Pages:', 'Страницы:' ),
	'Ništa nije pronađeno'                                                                    => array( 'Nothing found', 'Ничего не найдено' ),
	'Nažalost, ništa ne odgovara pretrazi. Probaj druge ključne reči.'                        => array( 'Sorry, nothing matches your search. Try other keywords.', 'К сожалению, ничего не найдено. Попробуйте другие ключевые слова.' ),
	'Ovde još nema sadržaja.'                                                                 => array( "There's no content here yet.", 'Здесь пока нет содержимого.' ),
	'Napravi svoj paket'                                                                      => array( 'Build your package', 'Собери свой набор' ),
	'Izaberi veličinu paketa, pa ubaci omiljene peškiriće. Cena po komadu pada sa svakim sledećim.' => array( 'Pick a package size, then drop in your favorite designs. The price per piece drops with each one.', 'Выбери размер набора, затем добавь любимые мотивы. Цена за штуку падает с каждым следующим.' ),
	'Izaberi veličinu paketa'                                                                 => array( 'Choose a package size', 'Выбери размер набора' ),
	'Ubaci svoje peškiriće'                                                                   => array( 'Add your designs', 'Добавь свои мотивы' ),
	'Izabrano'                                                                                => array( 'Selected', 'Выбрано' ),
	'Iznenadi me'                                                                             => array( 'Surprise me', 'Удиви меня' ),
	'Očisti'                                                                                  => array( 'Clear', 'Очистить' ),
	'Peškirići'                                                                               => array( 'Designs', 'Мотивы' ),
	'Peškirić %d'                                                                             => array( 'Design %d', 'Мотив %d' ),
	'Dodaj u korpu • %s'                                                                      => array( 'Add to cart • %s', 'В корзину • %s' ),
	'Izaberi još %d'                                                                          => array( 'Choose %d more', 'Выбери ещё %d' ),
	'peškirić'                                                                                => array( 'design', 'мотив' ),
	'peškirića'                                                                               => array( 'designs', 'мотива' ),
	'Najviše %d peškirića odjednom — dodaj ovaj paket u korpu, pa napravi sledeći'           => array( 'At most %d designs at once — add this package to the cart, then build the next', 'Не больше %d мотивов за раз — добавь этот набор в корзину и собери следующий' ),
	'Izaberi još %d — paket nije popunjen'                                                    => array( "Choose %d more — the package isn't full", 'Выбери ещё %d — набор не заполнен' ),
	'Ukloni peškirić'                                                                         => array( 'Remove design', 'Удалить мотив' ),
	'Dodato u paket — izaberi još %d'                                                         => array( 'Added to the package — choose %d more', 'Добавлено в набор — выбери ещё %d' ),
	'Paket je pun — dodaj u korpu ili izaberi još'                                           => array( 'The package is full — add it to the cart or choose more', 'Набор собран — добавь в корзину или выбери ещё' ),
	'Dodato — u paketu je %d peškirića'                                                      => array( 'Added — %d designs in the package', 'Добавлено — в наборе %d мотивов' ),
	'+ %s'                                                                                   => array( '+ %s', '+ %s' ),
	'Još %1$s do besplatne dostave (preko %2$s)'                                             => array( '%1$s more for free delivery (over %2$s)', 'Ещё %1$s до бесплатной доставки (от %2$s)' ),
	'U paket'                                                                                 => array( 'Add to pack', 'В набор' ),
	'Dodaj %s u paket'                                                                        => array( 'Add %s to the package', 'Добавить %s в набор' ),
	'Kupi 1 kom'                                                                              => array( 'Buy just one', 'Купить 1 шт' ),
	'Kupi %s, 1 kom'                                                                          => array( 'Buy %s, 1 piece', 'Купить %s, 1 шт' ),
	'Spoji %1$d peškirića u paket — ušteda %2$s'                                              => array( 'Bundle %1$d designs and save %2$s', 'Собери %1$d мотива в набор — экономия %2$s' ),
	'Dodajem…'                                                                                => array( 'Adding…', 'Добавляем…' ),
	'Dodavanje nije uspelo — pokušaj ponovo'                                                  => array( "Couldn't add to the cart — please try again", 'Не удалось добавить в корзину — попробуйте ещё раз' ),
	'U tvom domu'                                                                             => array( 'In your home', 'У тебя дома' ),
	'Tvoj kutak, malo mekši'                                                                  => array( 'Your corner, a little softer', 'Твой уголок, чуть мягче' ),
	'Pored lavaboa, na kuki ili na polici — peškirići se uklope u svaki dom i unesu trunku topline.' => array( 'By the sink, on a hook or a shelf — the towels fit into any home and add a touch of warmth.', 'У раковины, на крючке или на полке — полотенца впишутся в любой дом и добавят капельку тепла.' ),
	'Spremni za jutarnju rutinu'                                                              => array( 'Ready for the morning routine', 'Готовы к утренним делам' ),
	'Na kuki, uvek pri ruci'                                                                  => array( 'On the hook, always at hand', 'На крючке, всегда под рукой' ),
	'Stiže spremno za poklon'                                                                 => array( 'Arrives gift-ready', 'Приходит готовым к подарку' ),
	'Pauziraj video'                                                                          => array( 'Pause the video', 'Приостановить видео' ),
	'Pusti video'                                                                             => array( 'Play the video', 'Воспроизвести видео' ),
	'Otvaranje CosyPaw paketa'                                                                => array( 'Opening a CosyPaw package', 'Распаковка набора CosyPaw' ),
	'Svaki paket je mali poklon'                                                              => array( 'Every package is a little gift', 'Каждый набор — маленький подарок' ),
	'Pažljivo upakovano u CosyPaw kutiju, sa porukom dobrodošlice i mirisom lavande — idealno za rođendan, bebi šauer ili samo da nekog razmaziš.' => array( 'Carefully packed in a CosyPaw box, with a welcome note and a scent of lavender — perfect for a birthday, baby shower, or just to spoil someone.', 'Аккуратно упаковано в коробку CosyPaw, с приветственной открыткой и ароматом лаванды — идеально на день рождения, бэби-шауэр или просто чтобы кого-то побаловать.' ),
	'Jezik'                                                                                   => array( 'Language', 'Язык' ),
	'Komentari (%s)'                                                                          => array( 'Comments (%s)', 'Комментарии (%s)' ),
	'Prethodni'                                                                               => array( 'Previous', 'Предыдущие' ),
	'Sledeći'                                                                                 => array( 'Next', 'Следующие' ),
	'Komentari su zatvoreni.'                                                                 => array( 'Comments are closed.', 'Комментарии закрыты.' ),
	'Ostavi komentar'                                                                         => array( 'Leave a comment', 'Оставить комментарий' ),
	'Pošalji'                                                                                 => array( 'Send', 'Отправить' ),
	// Social proof (Utisci).
	'Zadovoljne mušterije'                                                                    => array( 'Happy customers', 'Довольные покупатели' ),
	'Mali peškirići, veliki osmesi'                                                           => array( 'Little towels, big smiles', 'Маленькие полотенца, большие улыбки' ),
	'Stigli su brže nego što sam očekivala i mekši su nego na slikama. Ćerka bira koji će da koristi svaki dan.' => array( 'They arrived faster than I expected and are softer than in the photos. My daughter picks which one to use every day.', 'Пришли быстрее, чем я ожидала, и мягче, чем на фото. Дочка сама выбирает, какое использовать каждый день.' ),
	'Kupila sam 2+1 paket za poklon i bio je pravi hit. Pakovanje je preslatko, ne moraš ništa dodatno da uvijaš.' => array( 'I bought the 2+1 package as a gift and it was a real hit. The packaging is adorable, no extra wrapping needed.', 'Купила набор 2+1 в подарок — и это был настоящий хит. Упаковка очаровательна, ничего дополнительно заворачивать не нужно.' ),
	'Alka za kačenje je sitnica koja mnogo znači — peškirić je uvek na svom mestu i ne završi na podu.' => array( 'The hanging loop is a small thing that means a lot — the towel is always in its place and never ends up on the floor.', 'Петелька — мелочь, которая много значит: полотенце всегда на своём месте и не падает на пол.' ),
	'%d od 5 zvezdica'                                                                        => array( '%d out of 5 stars', '%d из 5 звёзд' ),
	'Jovana M.'                                                                               => array( 'Jovana M.', 'Йована М.' ),
	'Milica P.'                                                                               => array( 'Milica P.', 'Милица П.' ),
	'Ana T.'                                                                                  => array( 'Ana T.', 'Ана Т.' ),
	'Novi Sad'                                                                                => array( 'Novi Sad', 'Нови-Сад' ),
	'Beograd'                                                                                 => array( 'Belgrade', 'Белград' ),
	'Niš'                                                                                     => array( 'Niš', 'Ниш' ),
	// The section's own ask — see front-page.php.
	'Sad si ti na redu'                                                                       => array( 'Your turn now', 'Теперь твоя очередь' ),
	'Stigao ti je peškirić? Napiši par reči — to je ono što sledećem kupcu pomogne da izabere.' => array( 'Has your towel arrived? Write a few words — that is what helps the next customer choose.', 'Полотенце уже у вас? Напишите пару слов — именно это помогает следующему покупателю выбрать.' ),
	'Ostavi utisak'                                                                           => array( 'Leave a review', 'Оставить отзыв' ),
	'Izaberi peškirić koji imaš — forma je na njegovoj stranici.'                             => array( 'Pick the towel you own — the form is on its page.', 'Выберите полотенце, которое у вас есть — форма на его странице.' ),
	// FAQ.
	'Česta pitanja'                                                                           => array( 'FAQ', 'Частые вопросы' ),
	'Sve što te zanima'                                                                       => array( 'Everything you want to know', 'Всё, что вас интересует' ),
	'Ako ne nađeš odgovor, piši nam — rado pomažemo.'                                         => array( "If you don't find an answer, write to us — we're happy to help.", 'Если не найдёте ответ — напишите нам, мы рады помочь.' ),
	'Od čega su peškirići napravljeni?'                                                       => array( 'What are the towels made of?', 'Из чего сделаны полотенца?' ),
	'Od plišane mikrofibre — mekane, lagane i jako upijajuće. Prijatna je i nežnoj dečjoj koži.' => array( 'From plush microfiber — soft, light and highly absorbent. Gentle even on a child\'s skin.', 'Из плюшевой микрофибры — мягкой, лёгкой и очень впитывающей. Приятна даже нежной детской коже.' ),
	'Kako se peru?'                                                                           => array( 'How are they washed?', 'Как их стирать?' ),
	'Mašinsko pranje na 40°C, bez omekšivača da ostanu upijajući. Suše se brzo i ne gube oblik.' => array( 'Machine wash at 40°C, without fabric softener so they stay absorbent. They dry fast and keep their shape.', 'Машинная стирка при 40°C, без кондиционера, чтобы сохранить впитываемость. Быстро сохнут и держат форму.' ),
	'Mogu li da ih koristim i za ljubimce?'                                                   => array( 'Can I use them for pets too?', 'Можно ли использовать их для питомцев?' ),
	'Mogu. Mnogi ih koriste kao peškir za pse — za brisanje šapa posle šetnje. Mikrofibra brzo upije vlagu, a peškir se lako opere u mašini.' => array( 'Yes. Many people use them as a dog towel — for wiping paws after a walk. Microfiber soaks up damp quickly, and the towel is easy to machine-wash.', 'Да. Многие используют их как полотенце для собаки — вытирать лапы после прогулки. Микрофибра быстро впитывает влагу, а полотенце легко стирается в машине.' ),
	'Koliko traje dostava?'                                                                   => array( 'How long does delivery take?', 'Сколько занимает доставка?' ),
	'Dostava je 2–4 radna dana na teritoriji cele Srbije. Za porudžbine preko %s dostava je besplatna.' => array( 'Delivery is 2–4 business days across Serbia. Orders over %s ship free.', 'Доставка 2–4 рабочих дня по всей Сербии. Заказы свыше %s доставляются бесплатно.' ),
	'Dostava je 2–4 radna dana na teritoriji cele Srbije.'                                    => array( 'Delivery is 2–4 business days across Serbia.', 'Доставка 2–4 рабочих дня по всей Сербии.' ),
	'Kako mogu da platim?'                                                                    => array( 'How can I pay?', 'Как я могу оплатить?' ),
	'Plaćanje je pouzećem — platiš kuriru pri preuzimanju paketa.'                            => array( 'Payment is cash on delivery — you pay the courier when you receive the package.', 'Оплата при получении — вы платите курьеру при получении посылки.' ),
	'Gde mogu da ostavim utisak?'                                                             => array( 'Where can I leave a review?', 'Где можно оставить отзыв?' ),
	'Na dnu početne strane, u delu sa utiscima kupaca, klikni „Ostavi utisak“ i izaberi peškirić koji imaš. Isto možeš i sa stranice svakog peškirića, u tabu „Recenzije“.' => array( 'At the bottom of the home page, in the customer reviews part, click “Leave a review” and pick the towel you own. You can also do it from any towel’s page, in the “Reviews” tab.', 'Внизу главной страницы, в разделе с отзывами покупателей, нажмите «Оставить отзыв» и выберите своё полотенце. То же можно сделать на странице любого полотенца, во вкладке «Отзывы».' ),

	// Checkout labels. CheckoutSetup writes these into the WooCommerce gateway
	// and shipping-zone settings in the source language; they are resolved back
	// through gettext per request, so every msgid has to live here.
	'Plaćanje pouzećem'                                                                       => array( 'Cash on delivery', 'Оплата при получении' ),
	'Plaćaš gotovinom pri preuzimanju — kuriru na vratima ili nama lično.'                    => array( 'You pay in cash on receipt — to the courier at your door, or to us in person.', 'Оплата наличными при получении — курьеру у двери или нам лично.' ),
	'Pripremi iznos u gotovini za trenutak preuzimanja.'                                      => array( 'Have the amount ready in cash for the moment of handover.', 'Приготовьте сумму наличными к моменту получения.' ),
	'Lično preuzimanje'                                                                       => array( 'Local pickup', 'Самовывоз' ),
	'Dostava kurirskom službom (poštarina se plaća kuriru)'                                   => array( 'Courier delivery (postage paid to the courier)', 'Доставка курьером (почтовые расходы оплачиваются курьеру)' ),

	// Cart-level package pricing (BundlePricing) and the floating cart pill.
	// The fee label reaches the order, so it has to resolve per locale too.
	// Hero — the offer ribbon, the buy button and the strip under it. The
	// numbers are placeholders because they are derived from the package the
	// builder opens on, not written into the copy.
	'Ručno rađeni peškiri sa alkom za kačenje — %d oblika koji grle tvoje kupatilo.'         => array( 'Handmade towels with a hanging loop — %d shapes that hug your bathroom.', 'Полотенца ручной работы с петелькой — %d форм, что обнимают твою ванную.' ),
	'%1$d+%2$d GRATIS'                                                                        => array( '%1$d+%2$d FREE', '%1$d+%2$d В ПОДАРОК' ),
	'besplatna dostava'                                                                       => array( 'free shipping', 'бесплатная доставка' ),
	'Uzmi %1$d — plati %2$d'                                                                  => array( 'Take %1$d — pay for %2$d', 'Возьми %1$d — заплати за %2$d' ),
	'ili pogledaj svih %d peškirića'                                                          => array( 'or see all %d towels', 'или посмотри все %d полотенец' ),

	'Ušteda na paketima'                                                                      => array( 'Package saving', 'Скидка за наборы' ),
	'Ušteda na paketima (%s)'                                                                 => array( 'Package saving (%s)', 'Скидка за наборы (%s)' ),
	'Još 1 peškirić za %s'                                                                    => array( '1 more towel for %s', 'Ещё 1 полотенце за %s' ),
	'Još 1 peškirić gratis'                                                                   => array( '1 more towel free', 'Ещё 1 полотенце бесплатно' ),

	// Package offer on the cart, the checkout and the thank-you page (Theme\Upsell).
	'Još malo, pa povoljnije'                                                                 => array( 'A little more, a little cheaper', 'Ещё немного — и выгоднее' ),
	'Još jedan peškirić košta %s — i dostava je na nama.'                                     => array( 'One more towel costs %s — and delivery is on us.', 'Ещё одно полотенце стоит %s — и доставка за наш счёт.' ),
	'Još jedan peškirić košta %1$s umesto pune cene — ušteda %2$s.'                           => array( 'One more towel costs %1$s instead of full price — you save %2$s.', 'Ещё одно полотенце стоит %1$s вместо полной цены — экономия %2$s.' ),
	'Još jedan peškirić je gratis — ukupna cena se ne menja.'                                 => array( 'One more towel is free — the total does not change.', 'Ещё одно полотенце бесплатно — сумма не изменится.' ),
	'Sledeći peškirić'                                                                        => array( 'Next towel', 'Следующее полотенце' ),
	'Gratis'                                                                                  => array( 'Free', 'Бесплатно' ),
	'Dodaj još jedan — ukupna cena ostaje ista.'                                              => array( 'Add one more — the total stays the same.', 'Добавь ещё одно — сумма останется прежней.' ),
	'Dodaj'                                                                                   => array( 'Add', 'Добавить' ),
	'Prethodni peškirići'                                                                     => array( 'Previous towels', 'Предыдущие полотенца' ),
	'Sledeći peškirići'                                                                       => array( 'Next towels', 'Следующие полотенца' ),
	'Ostvarena'                                                                               => array( 'Earned', 'Получена' ),
	'Još %s'                                                                                  => array( '%s to go', 'Ещё %s' ),
	'Prag se računa na zbir peškirića (%s) — ušteda na paketima se ne oduzima.' => array( 'The threshold counts the towels themselves (%s) — the package saving is not subtracted.', 'Порог считается по сумме полотенец (%s) — скидка за набор не вычитается.' ),
	'Dodaj još jedan peškirić i pređeš %s — dostava je onda na nama.'          => array( 'Add one more towel to pass %s — delivery is then on us.', 'Добавь ещё одно полотенце, чтобы превысить %s — доставка тогда за наш счёт.' ),
	'Dostava je besplatna preko %s.'                                                          => array( 'Delivery is free over %s.', 'Доставка бесплатна свыше %s.' ),
	'Još %s do besplatne dostave'                                                             => array( '%s more for free delivery', 'Ещё %s до бесплатной доставки' ),
	'Možda vam se svidi'                                                                      => array( 'You might like', 'Возможно, вам понравится' ),
	'Ostatak družine te čeka'                                                                 => array( 'The rest of the crew is waiting', 'Остальная компания тебя ждёт' ),

	// The per-piece price on a motif card (front-page.php).
	'%1$d kom · %2$s / kom'                                                                   => array( '%1$d pcs · %2$s / pc', '%1$d шт · %2$s / шт' ),
	'Napravi paket od %1$d peškirića po %2$s, počni sa oblikom %3$s'                          => array( 'Build a package of %1$d towels at %2$s each, starting with %3$s', 'Собрать набор из %1$d полотенец по %2$s, начиная с формы %3$s' ),

	// Post-delivery review request (Theme\ReviewRequest).
	'Kako su peškirići, %s?'                                                                  => array( 'How are the towels, %s?', 'Как полотенца, %s?' ),
	'Kako su peškirići?'                                                                      => array( 'How are the towels?', 'Как полотенца?' ),
	'Reci nam par reči o svojim peškirićima'                                                  => array( 'Tell us a few words about your towels', 'Расскажите пару слов о своих полотенцах' ),
	'Prošlo je nedelju dana otkad je paket stigao — taman dovoljno da se peškirići okače, isprobaju i operu bar jednom.' => array( 'A week has passed since the parcel arrived — just enough time to hang the towels, try them out and wash them at least once.', 'Прошла неделя с тех пор, как посылка пришла — как раз достаточно, чтобы повесить полотенца, испытать их и постирать хотя бы раз.' ),
	'Ako imaš minut, ostavi kratku recenziju. Pomaže drugima da izaberu, a nama da znamo šta da pravimo sledeće.' => array( 'If you have a minute, leave a short review. It helps others choose, and it tells us what to make next.', 'Если у вас есть минута, оставьте короткий отзыв. Это помогает другим выбрать, а нам — понять, что делать дальше.' ),
	'Hvala na poverenju — CosyPaw'                                                            => array( 'Thank you for your trust — CosyPaw', 'Спасибо за доверие — CosyPaw' ),

	// Pooled landing-page reviews (Theme\Reviews).
	'Kupac'                                                                                   => array( 'Customer', 'Покупатель' ),
	'o proizvodu %s'                                                                          => array( 'about %s', 'о товаре %s' ),
	'Budi prvi koji je ocenio %s.'                                                            => array( 'Be the first to review %s.', 'Оставь первый отзыв о %s.' ),
	'Pročitaj utiske kupaca'                                                                  => array( 'Read what customers say', 'Читать отзывы покупателей' ),

	// Single product page: bundle route and the shared spec list.
	'Dodaj u paket'                                                                           => array( 'Add to a package', 'Добавить в набор' ),
	'Cena po komadu pada sa svakim sledećim peškirićem'                                       => array( 'The price per towel drops with every one you add', 'Цена за штуку падает с каждым следующим полотенцем' ),
	'Uzmi više, plati manje'                                                                  => array( 'Take more, pay less', 'Бери больше — плати меньше' ),
	'%s / kom'                                                                                => array( '%s / pc', '%s / шт' ),
	'%s je u korpi.'                                                                          => array( '%s is in your cart.', '%s в корзине.' ),
	'Napravi paket'                                                                           => array( 'Build a package', 'Собрать набор' ),
	'Nastavi kupovinu'                                                                        => array( 'Keep shopping', 'Продолжить покупки' ),
	'Idi u korpu'                                                                             => array( 'Go to cart', 'В корзину' ),
	'Materijal'                                                                               => array( 'Fabric', 'Материал' ),
	'Plišana mikrofibra — mekana, lagana i jako upijajuća.'                                   => array( 'Plush microfibre — soft, light and highly absorbent.', 'Плюшевая микрофибра — мягкая, лёгкая и очень впитывающая.' ),
	'Kačenje'                                                                                 => array( 'Hanging', 'Подвешивание' ),
	'Alka za kačenje, da peškirić uvek stoji na svom mestu.'                                  => array( 'A hanging loop, so the towel always stays where it belongs.', 'Петелька для подвешивания, чтобы полотенце всегда было на своём месте.' ),
	'Održavanje'                                                                              => array( 'Care', 'Уход' ),
	'Mašinsko pranje na 40°C, bez omekšivača. Suši se brzo i ne gubi oblik.'                  => array( 'Machine wash at 40°C, no fabric softener. Dries fast and keeps its shape.', 'Машинная стирка при 40°C, без кондиционера. Быстро сохнет и держит форму.' ),
	'Dostava'                                                                                 => array( 'Delivery', 'Доставка' ),
	'Plaćanje pouzećem, isporuka 2–4 dana širom Srbije.'                                      => array( 'Cash on delivery, 2–4 days anywhere in Serbia.', 'Оплата при получении, доставка 2–4 дня по всей Сербии.' ),
);

// English-source msgid => [ serbian, russian ].
$en_source = array(
	'Nothing found.'                                                                                           => array( 'Ništa nije pronađeno.', 'Ничего не найдено.' ),
	'Primary Sidebar'                                                                                          => array( 'Glavna bočna traka', 'Основной сайдбар' ),
	'Appears beside posts and archives.'                                                                       => array( 'Pojavljuje se pored objava i arhiva.', 'Отображается рядом с записями и архивами.' ),
	'Primary Menu'                                                                                             => array( 'Glavni meni', 'Основное меню' ),
	'Footer Menu'                                                                                              => array( 'Meni u podnožju', 'Меню в подвале' ),
	'CosyPaw recommends the WooCommerce plugin. Shop features are disabled until it is installed and active.'  => array( 'CosyPaw preporučuje WooCommerce dodatak. Funkcije prodavnice su onemogućene dok se ne instalira i aktivira.', 'CosyPaw рекомендует плагин WooCommerce. Функции магазина отключены, пока он не установлен и не активирован.' ),
	'CosyPaw Seeder'                                                                                           => array( 'CosyPaw Seeder', 'CosyPaw Seeder' ),
	'Done — %d new product(s) created.'                                                                        => array( 'Gotovo — kreirano %d novih proizvoda.', 'Готово — создано %d новых товаров.' ),
	'Currently mapped: %1$d motif products, %2$d package products.'                                            => array( 'Trenutno mapirano: %1$d proizvoda peškirića, %2$d proizvoda paketa.', 'Сейчас сопоставлено: %1$d товаров-мотивов, %2$d товаров-наборов.' ),
	'Create / update CosyPaw products'                                                                         => array( 'Kreiraj / ažuriraj CosyPaw proizvode', 'Создать / обновить товары CosyPaw' ),
	'Restore missing product images'                                                                           => array( 'Vrati slike proizvoda koje nedostaju', 'Восстановить отсутствующие изображения товаров' ),
	'Done — %d product image(s) restored.'                                                                     => array( 'Gotovo — vraćeno %d slika proizvoda.', 'Готово — восстановлено %d изображений товаров.' ),
	'Re-uploads the photo of any existing motif product whose image was deleted, and fills in image title, alt text, caption and description wherever they are still empty. Text you have written yourself is never overwritten. This creates no products and changes no settings — use it if the shop grid is showing grey placeholders.' => array( 'Ponovo postavlja fotografiju svakog postojećeg proizvoda kome je slika obrisana i popunjava naslov, alt tekst, opis ispod slike i opis slike svuda gde su još prazni. Tekst koji si sam napisao nikada se ne prepisuje. Ne kreira proizvode i ne menja podešavanja — koristi ovo ako mreža prodavnice prikazuje sive praznine.', 'Заново загружает фотографию любого существующего товара, у которого изображение было удалено, и заполняет заголовок, alt-текст, подпись и описание там, где они ещё пусты. Написанный вами текст никогда не перезаписывается. Не создаёт товаров и не меняет настроек — используйте, если в сетке магазина серые заглушки.' ),
	'Create products from the catalogue'                                                                       => array( 'Kreiraj proizvode iz kataloga', 'Создать товары из каталога' ),
	'Safe to run repeatedly — existing products are skipped.'                                                  => array( 'Bezbedno za ponovno pokretanje — postojeći proizvodi se preskaču.', 'Можно запускать повторно — существующие товары пропускаются.' ),
	'Safe to run repeatedly — existing products are skipped. Note that it also recreates any catalogue motif that is no longer a product, sets up cash on delivery and the Serbia shipping zone if they are missing, and sets the site logo. On a shop that has been running for a while, prefer the button above.' => array( 'Bezbedno za ponovno pokretanje — postojeći proizvodi se preskaču. Imaj u vidu da ponovo kreira i svaki peškirić iz kataloga koji više nije proizvod, postavlja plaćanje pouzećem i zonu dostave za Srbiju ako ih nema, i postavlja logo sajta. Na prodavnici koja već radi, koristi radije dugme iznad.', 'Можно запускать повторно — существующие товары пропускаются. Учтите, что он также заново создаёт любой мотив из каталога, который больше не является товаром, настраивает оплату при получении и зону доставки по Сербии, если их нет, и задаёт логотип сайта. Для магазина, который уже работает, лучше использовать кнопку выше.' ),
	'You are not allowed to do this.'                                                                          => array( 'Nije vam dozvoljeno da ovo uradite.', 'У вас нет прав для этого действия.' ),
	'WooCommerce is not active.'                                                                               => array( 'WooCommerce nije aktivan.', 'WooCommerce не активен.' ),
	'This site cannot process AVIF images, and the CosyPaw motif photography ships in that format. Seeding will create the products but their images will be missing or thumbnail-less. AVIF needs WordPress 6.5 or newer plus GD/Imagick built with AVIF support — ask your host to enable it, then run the seeder again.' => array( 'Ovaj sajt ne može da obradi AVIF slike, a CosyPaw fotografije peškirića su u tom formatu. Seeder će kreirati proizvode, ali će njihove slike nedostajati ili biti bez sličica. AVIF zahteva WordPress 6.5 ili noviji i GD/Imagick sa AVIF podrškom — zatraži od hostinga da je uključi, pa ponovo pokreni seeder.', 'Этот сайт не может обрабатывать изображения AVIF, а фотографии мотивов CosyPaw поставляются именно в этом формате. Seeder создаст товары, но их изображения будут отсутствовать или остаться без миниатюр. Для AVIF нужен WordPress 6.5 или новее и GD/Imagick с поддержкой AVIF — попроси хостинг включить её и запусти seeder снова.' ),
	'CosyPaw — names & short description'                                                                      => array( 'CosyPaw — imena i kratak opis', 'CosyPaw — названия и краткое описание' ),
	'The title field above is the Serbian name. Fill these in to set the name shown to English and Russian visitors.' => array( 'Polje za naslov iznad je srpski naziv. Popuni ova polja da postaviš naziv koji vide posetioci na engleskom i ruskom.', 'Поле заголовка выше — сербское название. Заполни эти поля, чтобы задать название для англоязычных и русскоязычных посетителей.' ),
	'Leave a name empty to use the translation from the theme language files (shown greyed out).'              => array( 'Ostavi ime prazno da se koristi prevod iz jezičkih fajlova teme (prikazan sivo).', 'Оставь имя пустым, чтобы использовать перевод из языковых файлов темы (показан серым).' ),
	'Short description (%s)'                                                                                   => array( 'Kratak opis (%s)', 'Краткое описание (%s)' ),
	'Descriptions have no language-file fallback: an empty field shows the Serbian short description above. Write two or three sentences about this motif — the shared facts (fabric, hanging loop, washing) are printed under every product automatically, so there is no need to repeat them.' => array( 'Opisi nemaju rezervu u jezičkim fajlovima: prazno polje prikazuje srpski kratak opis iznad. Napiši dve-tri rečenice o ovom obliku — zajedničke činjenice (materijal, alka, pranje) štampaju se ispod svakog proizvoda automatski, pa ih ne treba ponavljati.', 'У описаний нет резерва в языковых файлах: пустое поле покажет сербское краткое описание выше. Напиши две-три фразы об этой форме — общие факты (материал, петелька, стирка) печатаются под каждым товаром автоматически, повторять их не нужно.' ),
	// WooCommerce page titles (DB content, translated via the_title for the WC page IDs).
	'Cart'                                                                                                     => array( 'Korpa', 'Корзина' ),
	'Checkout'                                                                                                 => array( 'Plaćanje', 'Оформление заказа' ),
	'Shop'                                                                                                     => array( 'Prodavnica', 'Магазин' ),
	'My account'                                                                                               => array( 'Moj nalog', 'Мой аккаунт' ),

	// WooCommerce settings that were saved in wp-admin before CheckoutSetup
	// existed, so they hold WooCommerce's English defaults rather than the
	// Serbian strings configure() writes. Settings text is DB content and no
	// locale reaches it, which is why these are msgids here — see
	// Theme\CheckoutSetup for where they are resolved.
	'Cash on delivery'                                                                                         => array( 'Plaćanje pouzećem', 'Оплата при получении' ),
	'Pay with cash upon delivery.'                                                                             => array( 'Plaćaš gotovinom pri preuzimanju — kuriru na vratima ili nama lično.', 'Оплата наличными при получении — курьеру у двери или нам лично.' ),
	'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our [privacy_policy].' => array( 'Tvoji lični podaci koriste se za obradu porudžbine, za bolje iskustvo na ovom sajtu i za druge svrhe opisane u dokumentu [privacy_policy].', 'Ваши персональные данные используются для обработки заказа, для улучшения работы с сайтом и в других целях, описанных в документе [privacy_policy].' ),
);

// Assemble per-locale maps.
$en_US = array();
$ru_RU = array();
$sr_RS = array();
foreach ( $sr_source as $id => $t ) {
	$en_US[ $id ] = $t[0];
	$ru_RU[ $id ] = $t[1];
}
foreach ( $en_source as $id => $t ) {
	$sr_RS[ $id ] = $t[0];
	$ru_RU[ $id ] = $t[1];
}

$dir = dirname( __DIR__ ) . '/languages';
if ( ! is_dir( $dir ) ) {
	mkdir( $dir, 0755, true );
}

write_locale( $dir, 'en_US', $en_US );
write_locale( $dir, 'ru_RU', $ru_RU );
write_locale( $dir, 'sr_RS', $sr_RS );

echo 'Built: en_US (' . count( $en_US ) . '), ru_RU (' . count( $ru_RU ) . '), sr_RS (' . count( $sr_RS ) . ")\n";

/**
 * Write both .po and .mo for one locale.
 *
 * @param string               $dir    Languages directory.
 * @param string               $locale Locale code.
 * @param array<string,string> $map    msgid => msgstr.
 */
function write_locale( string $dir, string $locale, array $map ): void {
	$map = array_filter( $map, static fn( $v ) => '' !== $v );

	$header = "Content-Type: text/plain; charset=UTF-8\nLanguage: {$locale}\nMIME-Version: 1.0\nContent-Transfer-Encoding: 8bit\n";

	// --- .po ---
	$po = "msgid \"\"\nmsgstr \"\"\n";
	foreach ( explode( "\n", trim( $header ) ) as $line ) {
		$po .= '"' . po_escape( $line ) . '\n"' . "\n";
	}
	$po .= "\n";
	foreach ( $map as $id => $str ) {
		$po .= 'msgid "' . po_escape( $id ) . "\"\n";
		$po .= 'msgstr "' . po_escape( $str ) . "\"\n\n";
	}
	file_put_contents( "{$dir}/{$locale}.po", $po );

	// --- .mo (binary) ---
	$entries     = $map;
	$entries[''] = $header; // gettext metadata header.
	ksort( $entries, SORT_STRING );

	$ids  = array_keys( $entries );
	$n    = count( $entries );
	$base = 28 + $n * 8 + $n * 8; // header + original table + translation table (hash size 0).

	$orig_tbl  = '';
	$trans_tbl = '';
	$orig_buf  = '';
	$trans_buf = '';

	foreach ( $ids as $id ) {
		$orig_tbl .= pack( 'VV', strlen( $id ), $base + strlen( $orig_buf ) );
		$orig_buf .= $id . "\0";
	}
	$tbase = $base + strlen( $orig_buf );
	foreach ( $ids as $id ) {
		$str        = $entries[ $id ];
		$trans_tbl .= pack( 'VV', strlen( $str ), $tbase + strlen( $trans_buf ) );
		$trans_buf .= $str . "\0";
	}

	$mo  = pack( 'V', 0x950412de ); // magic (little-endian).
	$mo .= pack( 'V', 0 );          // revision.
	$mo .= pack( 'V', $n );         // string count.
	$mo .= pack( 'V', 28 );         // original table offset.
	$mo .= pack( 'V', 28 + $n * 8 );// translation table offset.
	$mo .= pack( 'V', 0 );          // hash size.
	$mo .= pack( 'V', 28 + $n * 8 + $n * 8 ); // hash offset.
	$mo .= $orig_tbl . $trans_tbl . $orig_buf . $trans_buf;

	file_put_contents( "{$dir}/{$locale}.mo", $mo );
}

/**
 * Escape a string for a .po literal.
 *
 * @param string $s Raw string.
 * @return string
 */
function po_escape( string $s ): string {
	return str_replace( array( '\\', '"', "\n" ), array( '\\\\', '\\"', '\\n' ), $s );
}
