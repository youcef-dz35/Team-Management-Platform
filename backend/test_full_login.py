import requests
import json

session = requests. Session()

print("=" * 60)
print("FULL LOGIN FLOW TEST")
print("=" * 60)

# Step 1: Get CSRF cookie
print("\n[1/2] Fetching CSRF cookie...")
csrf_response = session.get(
    "http://localhost/sanctum/csrf-cookie",
    headers={
        "Accept": "application/json",
        "Origin": "http://localhost:5173",
        "Referer": "http://localhost:5173/",
    }
)
print(f"   Status: {csrf_response.status_code}")
print(f"   XSRF-TOKEN: {session.cookies.get('XSRF-TOKEN', 'NOT SET')[:50]}...")

# Step 2: Extract XSRF token and attempt login
xsrf_token = session.cookies.get('XSRF-TOKEN', '')
print(f"\n[2/2] Attempting login with CSRF token...")

login_response = session.post(
    "http://localhost/api/v1/auth/login",
    headers={
        "Accept": "application/json",
        "Content-Type": "application/json",
        "Origin": "http://localhost:5173",
        "Referer": "http://localhost:5173/",
        "X-XSRF-TOKEN": xsrf_token,
    },
    json={
        "email": "ceo@example.com",
        "password": "password123"
    }
)

print(f"   Status: {login_response.status_code}")
print(f"   Response: {login_response.text[:200]}")

if login_response.status_code == 200:
    print("\n✅ LOGIN SUCCESSFUL!")
    data = login_response.json()
    print(f"   User: {data.get('user', {}).get('name', 'N/A')}")
    print(f"   Token: {data.get('token', 'N/A')[:50]}...")
elif login_response.status_code == 419:
    print("\n❌ CSRF TOKEN MISMATCH (419 error)")
else:
    print(f"\n❌ LOGIN FAILED: {login_response.status_code}")
