import sqlite3

conn = sqlite3.connect("../users.db")
cur = conn.cursor()

cur.execute("ALTER TABLE users ADD COLUMN balance REAL DEFAULT 0;")
cur.execute(
    """CREATE TABLE IF NOT EXISTS "transactions" (
	"id"	INTEGER NOT NULL,
	"username"	TEXT NOT NULL,
	"type"	TEXT NOT NULL DEFAULT 'Charge',
	"amount"	INTEGER,
	"description"	TEXT,
	"time"	INTEGER NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT),
	FOREIGN KEY("username") REFERENCES "users"("username")
);"""
)

conn.commit()

vouchers = cur.execute("SELECT * FROM vouchers").fetchall()
for voucher in vouchers:
    # print(voucher)
    voucher_id = voucher[0]
    username = voucher[1]
    amount = voucher[2]
    time_given = voucher[3]
    used = voucher[4]
    secret = voucher[5]
    if not used:
        cur.execute(
            f'UPDATE users SET balance = (SELECT balance FROM users WHERE username = "{username}") + {amount} WHERE username = "{username}";'
        )
        cur.execute(
            "INSERT INTO transactions(username, type, amount, description, time) VALUES(?, ?, ?, ?, ?)",
            (username, "Migration", amount, f"Voucher {voucher_id}", time_given),
        )

conn.commit()
