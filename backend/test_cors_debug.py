import requests
import json

session = requests.Session()

print("=" * 70)
print("CORS & COOKIE DEBUG TEST")
print("=" * 70)

# Step 1: Test CSRF cookie endpoint
print("\n[STEP 1] Testing CSRF Cookie Endpoint")
print("-" * 70)
csrf_response = session.get(
    "http://localhost/sanctum/csrf-cookie",
    headers={
        "Accept": "application/json",
        "Origin": "http://localhost:5173",
        "Referer": "http://localhost:5173/",
    }
)

print(f"Status Code: {csrf_response.status_code}")
print(f"\nResponse Headers:")
for key, value in csrf_response.headers.items():
    if 'access-control' in key.lower() or 'set-cookie' in key.lower():
        print(f"  {key}: {value}")

print(f"\nCookies Received:")
for cookie in session.cookies:
    print(f"  {cookie.name}: {cookie.value[:50]}...")
    print(f"    Domain: {cookie.domain}, Path: {cookie.path}")
    print(f"    Secure: {cookie.secure}, HttpOnly: {cookie.has_nonstandard_attr('HttpOnly')}")

xsrf_token = session.cookies.get('XSRF-TOKEN', '')
laravel_session = session.cookies.get('laravel_session', '')

print(f"\nExtracted XSRF-TOKEN: {xsrf_token[:50] if xsrf_token else 'NOT SET'}...")
print(f"Extracted laravel_session: {laravel_session[:50] if laravel_session else 'NOT SET'}...")

# Step 2: Test login with debug
print("\n[STEP 2] Testing Login Request")
print("-" * 70)

login_headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
    "Origin": "http://localhost:5173",
    "Referer": "http://localhost:5173/login",
    "X-XSRF-TOKEN": xsrf_token,
}

print("Request Headers:")
for key, value in login_headers.items():
    if key == "X-XSRF-TOKEN":
        print(f"  {key}: {value[:50]}...")
    else:
        print(f"  {key}: {value}")

print("\nCookies Being Sent:")
for cookie in session.cookies:
    print(f"  {cookie.name}: {cookie.value[:50]}...")

login_response = session.post(
    "http://localhost/api/v1/auth/login",
    headers=login_headers,
    json={
        "email": "ceo@example.com",
        "password": "password123"
    }
)

print(f"\nLogin Response Status: {login_response.status_code}")
print(f"\nLogin Response Headers:")
for key, value in login_response.headers.items():
    if 'access-control' in key.lower() or 'set-cookie' in key.lower():
        print(f"  {key}: {value}")

print(f"\nResponse Body:")
try:
    print(json.dumps(login_response.json(), indent=2)[:500])
except:
    print(login_response.text[:500])

print("\n" + "=" * 70)
if login_response.status_code == 200:
    print("✅ LOGIN SUCCESSFUL")
elif login_response.status_code == 419:
    print("❌ CSRF TOKEN MISMATCH (419)")
    print("\nPossible causes:")
    print("  1. Cookie domain mismatch (frontend vs backend)")
    print("  2. SameSite attribute blocking cookies")
    print("  3. Session not persisting between requests")
    print("  4. XSRF token encryption/decryption issue")
else:
    print(f"❌ LOGIN FAILED: {login_response.status_code}")
print("=" * 70)
