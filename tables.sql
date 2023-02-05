CREATE TABLE history (
    id_stock INTEGER,
    unix     INTEGER,
    date     TEXT,
    open     DOUBLE,
    high     DOUBLE,
    low      DOUBLE,
    close    DOUBLE,
    volume   INTEGER,
    UNIQUE (
        id_stock,
        date
    )
    ON CONFLICT REPLACE
);

CREATE TABLE instruments (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    id_post            INTEGER,
    id_stock           INTEGER,
    currency           TEXT,
    relativeDailyYield DOUBLE,
    lastPrice          DOUBLE,
    price              DOUBLE,
    relativeYield      DOUBLE,
    UNIQUE (
        id_post,
        id_stock
    )
    ON CONFLICT IGNORE
);

CREATE TABLE notfound_profile (
    nickname TEXT UNIQUE ON CONFLICT IGNORE
);

CREATE TABLE posts (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    id_profile    INTEGER,
    id_api        TEXT,
    likesCount    INTEGER,
    commentsCount INTEGER,
    inserted      INTEGER,
    UNIQUE (
        id_profile,
        id_api
    )
    ON CONFLICT IGNORE
);

CREATE TABLE profile (
    id                     INTEGER PRIMARY KEY AUTOINCREMENT,
    id_api                 TEXT    UNIQUE ON CONFLICT REPLACE,
    nickname               TEXT,
    update_info            INTEGER,
    update_posts           INTEGER,
    type                   TEXT,
    status                 TEXT,
    followersCount         INTEGER,
    followingCount         INTEGER,
    yearRelativeYield      DOUBLE,
    monthOperationsCount   INTEGER,
    totalAmountRange_lower INTEGER,
    totalAmountRange_upper INTEGER,
    json                   TEXT
);

CREATE TABLE stocks (
    id     INTEGER PRIMARY KEY AUTOINCREMENT,
    ticker TEXT    UNIQUE ON CONFLICT IGNORE
);

CREATE TABLE urls (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    url             TEXT    UNIQUE ON CONFLICT IGNORE,
    status          INTEGER DEFAULT (0),
    [insert]        INTEGER,
    [update]        INTEGER,
    code            INTEGER,
    redirects       TEXT,
    count_find_urls INTEGER
);

-- Представления

SELECT s.ticker, pf.id as id_profile, p.inserted, i.* FROM instruments i
join stocks s
on i.id_stock = s.id
join posts p
on i.id_post = p.id
join profile pf
on p.id_profile = pf.id


SELECT pf.id as id_profile, pf.nickname, p.inserted, count(i.id) as cnt_instrs FROM instruments i
join posts p
on i.id_post = p.id
join profile pf
on p.id_profile = pf.id
GROUP BY pf.nickname