import mysql.connector

db = mysql.connector.connect(
    host='127.0.0.1',
    user='root',
    password='',
    database='rfid_balung'
)
cursor = db.cursor()
cursor.execute("UPDATE pegawai SET jabatan = 'DEVELOPER SYSTEM' WHERE nama_lengkap LIKE '%Edwin%' OR id_mesin = 2")
db.commit()
print(f"Successfully updated {cursor.rowcount} row(s) for Edwin's jabatan to DEVELOPER SYSTEM!")
