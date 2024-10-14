import sqlite3

conn = sqlite3.connect("users.db")
cur = conn.cursor()

cur.execute("ALTER TABLE users DROP COLUMN token;")
cur.execute("ALTER TABLE users DROP COLUMN expires_at;")
cur.execute(
    """CREATE TABLE IF NOT EXISTS "sessions" (
	"username"	TEXT NOT NULL,
	"token"	TEXT NOT NULL
	"expires_at"	INTEGER NOT NULL,
	PRIMARY KEY("token"),
	FOREIGN KEY("username") REFERENCES "users"("username")
);"""
)

conn.commit()