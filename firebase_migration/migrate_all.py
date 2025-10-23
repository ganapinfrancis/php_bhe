import mysql.connector
import firebase_admin
from firebase_admin import credentials, firestore
import datetime
import decimal  # 🧮 para makilala ang Decimal types

# 🔹 Firebase setup
cred = credentials.Certificate("smart-scholar-mayao-kanluran-firebase-adminsdk-fbsvc-9b1cc4c834.json")
firebase_admin.initialize_app(cred)
db = firestore.client()

# 🔹 MySQL setup (XAMPP)
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="smart_scholar_db"
)
cursor = conn.cursor()

# 🔹 Kunin lahat ng table names sa database
cursor.execute("SHOW TABLES")
tables = cursor.fetchall()

print(f"📦 Found {len(tables)} tables in database: {tables}")

for (table_name,) in tables:
    print(f"\n🚀 Migrating table: {table_name}")
    cur = conn.cursor(dictionary=True)
    cur.execute(f"SELECT * FROM {table_name}")
    rows = cur.fetchall()

    if not rows:
        print(f"⚠️ Skipping '{table_name}' — empty table.")
        continue

    for row in rows:
        # 🔹 Convert MySQL DATE/DATETIME and DECIMAL values
        for key, value in row.items():
            if isinstance(value, (datetime.date, datetime.datetime)):
                row[key] = value.isoformat()  # convert date → string
            elif isinstance(value, decimal.Decimal):
                row[key] = float(value)  # convert Decimal → float

        # 🔹 Upload to Firestore
        doc_ref = db.collection(table_name).document()
        doc_ref.set(row)

    print(f"✅ Done migrating '{table_name}' — {len(rows)} records uploaded.")

print("\n🎉 Migration complete for all tables!")

cursor.close()
conn.close()
