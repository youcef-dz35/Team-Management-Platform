import requests
import urllib.parse

session = requests.Session()

print("=" * 70)
print("DETAILED CSRF DEBUG TEST")
print("=" * 70)

# Step 1: Get CSRF cookie
print("\n[STEP 1] Fetching CSRF cookie from /sanctum/csrf-cookie...")
csrf_response = session.get(
    "http://localhost/sanctum/csrf-cookie",
    headers={
        "Accept": "application/json",
        "Origin": "http://localhost:5173",
        "Referer": "http://localhost:5173/",
    }
)
print(f"  Status: {csrf_response.status_code}")
print(f"  Set-Cookie headers:")
for header, value in csrf_response.headers.items():
    if 'set-cookie' in header.lower():
        print(f"    {header}: {value[:100]}...")

# Show all cookies
print(f"\n[STEP 2] Cookies in session:")
for cookie in session.cookies:
    print(f"  {cookie.name}:")
    print(f"    Value (first 80 chars): {cookie.value[:80]}...")
    print(f"    Domain: {cookie.domain}")
    print(f"    Path: {cookie.path}")

# Extract XSRF token (URL-decoded)
xsrf_token_raw = session.cookies.get('XSRF-TOKEN', '')
xsrf_token_decoded = urllib.parse.unquote(xsrf_token_raw)

print(f"\n[STEP 3] XSRF Token comparison:")
print(f"  Raw (URL-encoded, first 80): {xsrf_token_raw[:80]}...")
print(f"  Decoded (first 80): {xsrf_token_decoded[:80]}...")
print(f"  Are they different? {xsrf_token_raw != xsrf_token_decoded}")

# Show session cookie
session_cookie = session.cookies.get('team_management_platform_session', '')
print(f"\n[STEP 4] Session cookie:")
print(f"  First 80 chars: {session_cookie[:80]}...")

# Try login with RAW token (what browser would send)
print(f"\n[STEP 5] Attempting login with DECODED X-XSRF-TOKEN header...")
login_response = session.post(
    "http://localhost/api/v1/auth/login",
    headers={
        "Accept": "application/json",
        "Content-Type": "application/json",
        "Origin": "http://localhost:5173",
        "Referer": "http://localhost:5173/",
        "X-XSRF-TOKEN": xsrf_token_decoded,  # Send DECODED token
    },
    json={
        "email": "ceo@example.com",
        "password": "password123"
    }
)

print(f"  Status: {login_response.status_code}")
if login_response.status_code == 200:
    print("  ✅ LOGIN SUCCESSFUL with decoded token!")
elif login_response.status_code == 419:
    print("  ❌ Still 419 with decoded token")
    print(f"  Response: {login_response.text[:200]}")
