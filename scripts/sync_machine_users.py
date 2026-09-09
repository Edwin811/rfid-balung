from zk import ZK

def sync():
    # Gunakan force_udp=False (TCP Mode)
    zk = ZK('169.254.176.250', port=4370, timeout=10, force_udp=False)
    conn = None
    try:
        conn = zk.connect()
        conn.disable_device()
        print('[*] Terhubung ke Mesin ZKTeco (TCP Mode)')

        # Total 16 User (ID 1 & 2 ADMIN/Edwin + 14 Perangkat Desa resmi)
        users_data = [
            {'user_id': '1', 'name': 'ADMIN', 'card': 8811783},
            {'user_id': '2', 'name': 'Edwin', 'card': 2660909},
            {'user_id': '3', 'name': 'IMAM MUSTOFA', 'card': 1003},
            {'user_id': '4', 'name': 'MOH. ANSORI', 'card': 1004},
            {'user_id': '5', 'name': 'SHOLEH A R', 'card': 1005},
            {'user_id': '6', 'name': 'SITI AMINAH', 'card': 1006},
            {'user_id': '7', 'name': 'KHOIRUL ANAM', 'card': 1007},
            {'user_id': '8', 'name': 'DIAN CANDRA DINATA', 'card': 1008},
            {'user_id': '9', 'name': 'HUSNAN SUBANDI', 'card': 1009},
            {'user_id': '10', 'name': 'SUTOMO', 'card': 1010},
            {'user_id': '11', 'name': 'ASMAD', 'card': 1011},
            {'user_id': '12', 'name': 'NUR ROHMAT', 'card': 1012},
            {'user_id': '13', 'name': 'NURHOLIK', 'card': 1013},
            {'user_id': '14', 'name': 'SUGENG RAWUH', 'card': 1014},
            {'user_id': '15', 'name': 'ANANG SETIAWAN', 'card': 1015},
            {'user_id': '16', 'name': 'ABDUL WAHIT', 'card': 1016},
        ]

        for u in users_data:
            uid = int(u['user_id'])
            print(f"[*] Set UserID {u['user_id']} -> {u['name']}")
            conn.set_user(uid=uid, name=u['name'], privilege=0, password='', group_id='0', user_id=u['user_id'], card=u['card'])

        print("\n[OK] Daftar 16 User Terbaru pada Mesin ZKTeco:")
        updated_users = conn.get_users()
        for u in updated_users:
            print(f"  UID: {u.uid} | UserID: {u.user_id} | Nama: {u.name} | No Kartu: {u.card}")

    except Exception as e:
        print(f"[ERROR] {e}")
    finally:
        if conn:
            conn.enable_device()
            conn.disconnect()
            print("[*] Koneksi ke mesin ditutup.")

if __name__ == '__main__':
    sync()
