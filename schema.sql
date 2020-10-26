CREATE TABLE IF NOT EXISTS onchat (
    date TEXT,
    username TEXT,
    PRIMARY KEY (date, username)
);

CREATE TABLE IF NOT EXISTS streamers (
    username TEXT PRIMARY KEY
);
