CREATE DATABASE soybase;
USE soybase;

CREATE TABLE soybase (
	id integer PRIMARY KEY,
    feature_name VARCHAR(255),
    feature_source_version VARCHAR(255),
    similog_name VARCHAR(255),
    target_source_version VARCHAR(255)
);

INSERT INTO soybase (id, feature_name, feature_source_version, similog_name, target_source_version)
VALUES
(138416,"Glyma.01g000100","Wm82.a4.v1","Glyma01g00210","Glyma 1.1"),
(138426,"Glyma.01g000100","Wm82.a4.v1","Glyma01g000100","Glyma2.0"),
(138425,"Glyma.01g000400","Wm82.a4.v1","Glyma01g00300","Glyma 1.1"),
(138422,"Glyma.01g000400","Wm82.a4.v1","Glyma01g00300","Glyma 1.1"),
(138423,"Glyma.01g000400","Wm82.a4.v1","Glyma01g000400","Glyma2.0"),
(138421,"Glyma.01g000600","Wm82.a4.v1","Glyma01g00321","Glyma 1.1"),
(138427,"Glyma.01g000600","Wm82.a4.v1","Glyma01g000600","Glyma2.0"),
(138438,"Glyma.01g001000","Wm82.a4.v1","Glyma01g00400","Glyma 1.1"),
(138429,"Glyma.01g001000","Wm82.a4.v1","Glyma01g001000","Glyma2.0"),
(138428,"Glyma.01g001100","Wm82.a4.v1","Glyma01g00410","Glyma 1.1")