#!/usr/bin/env python3
import urllib.parse
import http.client
import json

print("=" * 70)
print("CSRF DEBUGGING - URL ENCODING TEST")
print("=" * 70)

# Step 1: Get CSRF cookie
conn = http.client.HTTPConnection("localhost", 80)
headers = {
    "Accept": "application/json",
    "Origin": "http://localhost:5173",
    "Referer": "http://localhost:5173/",
}

conn.request("GET", "/sanctum/csrf-cookie", headers=headers)
response = conn.getresponse()
print(f"\n[1] CSRF Cookie Request: {response.status} {response.reason}")

# Parse Set-Cookie headers - get both XSRF-TOKEN and laravel_session
xsrf_token = None
laravel_session = None

for header, value in response.getheaders():
    if header.lower() == 'set-cookie':
        # Parse cookie
        parts = value.split(';')
        name_value = parts[0].split('=', 1)
        if len(name_value) == 2:
            name, val = name_value
            if name == 'XSRF-TOKEN':
                xsrf_token = val
                print(f"\n  XSRF-TOKEN (URL-encoded): {val[:60]}...")
                print(f"  XSRF-TOKEN (decoded): {urllib.parse.unquote(val)[:60]}...")
            elif name == 'laravel_session':
                laravel_session = val
                print(f"\n  laravel_session (URL-encoded): {val[:60]}...")

response.read()
conn.close()

if not xsrf_token or not laravel_session:
    print("\n❌ Missing cookies!")
    if not xsrf_token:
        print("  - XSRF-TOKEN not set")
    if not laravel_session:
        print("  - laravel_session not set")
    exit(1)

# Step 2: Login with properly formatted cookies
print("\n\n" + "=" * 70)
print("[2] Login Request")
print("=" * 70)

conn = http.client.HTTPConnection("localhost", 80)

# IMPORTANT: Cookies should be sent URL-encoded in Cookie header
# But X-XSRF-TOKEN header should be URL-DECODED
cookie_header = f"XSRF-TOKEN={xsrf_token}; laravel_session={laravel_session}"
xsrf_header_value = urllib.parse.unquote(xsrf_token)

print(f"\nCookie header: {cookie_header[:80]}...")
print(f"X-XSRF-TOKEN header: {xsrf_header_value[:80]}...")

login_headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
    "Origin": "http://localhost:5173",
    "Referer": "http://localhost:5173/login",
    "X-XSRF-TOKEN": xsrf_header_value,  # DECODED!
    "Cookie": cookie_header,  # ENCODED!
}

body = json.dumps({
    "email": "ceo@example.com",
    "password": "password123"
})

conn.request("POST", "/api/v1/auth/login", body=body, headers=login_headers)
response = conn.getresponse()

print(f"\nResponse: {response.status} {response.reason}")

response_body = response.read().decode('utf-8')
print(f"\nBody: {response_body[:400]}")

conn.close()

print("\n" + "=" * 70)
if response.status == 200:
    print("✅ LOGIN SUCCESSFUL!")
elif response.status == 419:
    print("❌ CSRF TOKEN MISMATCH (419)")
    print("\nTroubleshooting:")
    print("  - Check if session cookie is being sent correctly")
    print("  - Verify X-XSRF-TOKEN header is URL-decoded")
    print("  - Confirm Cookie header contains URL-encoded values")
else:
    print(f"❌ UNEXPECTED STATUS: {response.status}")
print("=" * 70)
