BEGIN TRANSACTION;
CREATE TABLE "news_art_works" (
	`news_id`	INTEGER NOT NULL,
	`art_works_id`	INTEGER NOT NULL,
	FOREIGN KEY(`news_id`) REFERENCES news ( id ),
	FOREIGN KEY(`art_works_id`) REFERENCES art_works ( id )
);
CREATE TABLE "news" (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`title_en`	TEXT,
	`title_uk`	TEXT,
	`slug`	TEXT NOT NULL UNIQUE,
	`type`	TEXT NOT NULL,
	`picture`	TEXT,
	`images`	TEXT,
	`text_en`	TEXT,
	`text_uk`	TEXT,
	`date`	TEXT NOT NULL,
	`date_unix`	INTEGER NOT NULL
);
CREATE TABLE "art_works" (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`title_en`	TEXT NOT NULL,
	`title_uk`	TEXT NOT NULL,
	`description_en`	TEXT,
	`description_uk`	TEXT,
	`slug`	TEXT NOT NULL UNIQUE,
	`price`	INTEGER,
	`in_stock`	INTEGER NOT NULL,
	`on_front`	INTEGER NOT NULL,
	`picture`	TEXT NOT NULL,
	`images`	TEXT,
	`width`	INTEGER,
	`height`	INTEGER,
	`date`	TEXT NOT NULL,
	`date_unix`	INTEGER NOT NULL,
	`materials_en`	TEXT,
	`materials_uk`	INTEGER
);
CREATE UNIQUE INDEX primary_index ON news_art_works(news_id, art_works_id);
CREATE INDEX `news_slug` ON `news` (`slug` )




;
COMMIT;
