import sqlite3

conn = sqlite3.connect("../users.db")
cur = conn.cursor()

cur.execute("ALTER TABLE users ADD COLUMN flags INTEGER DEFAULT 0;")
cur.execute(
"""CREATE TABLE IF NOT EXISTS "flags" (
	"index"	INTEGER NOT NULL,
	"name"	INTEGER NOT NULL,
	PRIMARY KEY("index" AUTOINCREMENT)
);"""
)
cur.execute("INSERT INTO flags (name) VALUES ('admin');")
cur.execute("INSERT INTO flags (name) VALUES ('scan');")
cur.execute("INSERT INTO flags (name) VALUES ('self_charge');")
conn.commit()

cur.execute("SELECT username, access_level FROM users;")
users = cur.fetchall()
for usr in users:
    if usr[1] == 1:
        cur.execute("UPDATE users SET flags = 1 WHERE username = ?;", (usr[0],))
    elif usr[1] == -1:
        cur.execute("UPDATE users SET flags = 2 WHERE username = ?;", (usr[0],))

cur.execute("ALTER TABLE users DROP COLUMN access_level;")

conn.commit()