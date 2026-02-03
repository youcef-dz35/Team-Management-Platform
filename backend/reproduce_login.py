import requests
import time
import sys

BASE_URL = "http://localhost"
LOGIN_URL = f"{BASE_URL}/api/v1/auth/login"
CSRF_URL = f"{BASE_URL}/sanctum/csrf-cookie"

def test_login():
    session = requests.Session()
    
    # 1. Get CSRF Cookie
    print(f"Fetching CSRF cookie from {CSRF_URL}...")
    start = time.time()
    try:
        r_csrf = session.get(CSRF_URL)
        print(f"CSRF took: {time.time() - start:.2f}s")
        print(f"CSRF Status: {r_csrf.status_code}")
    except Exception as e:
        print(f"Failed to fetch CSRF: {e}")
        return

    if 'XSRF-TOKEN' not in session.cookies:
        print("ERROR: No XSRF-TOKEN in cookies!")
        print("Cookies:", session.cookies.get_dict())
        return

    xsrf_token = session.cookies['XSRF-TOKEN']
    # Decode if needed (requests handles quoting usually, but Laravel expects unquoted in header)
    from urllib.parse import unquote
    xsrf_token = unquote(xsrf_token)

    # 2. Login
    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-XSRF-TOKEN": xsrf_token,
        "Origin": "http://localhost:5173",
        "Referer": "http://localhost:5173/"
    }
    
    payload = {
        "email": "ceo@example.com",
        "password": "password"
    }

    print(f"\nAttempting Login...")
    start = time.time()
    try:
        r_login = session.post(LOGIN_URL, json=payload, headers=headers)
        duration = time.time() - start
        print(f"Login took: {duration:.2f}s")
        print(f"Login Status: {r_login.status_code}")
        print(f"Response: {r_login.text}")
    except Exception as e:
        print(f"Login Request Failed: {e}")

if __name__ == "__main__":
    test_login()
