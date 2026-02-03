#!/usr/bin/env python3
import urllib.parse
import http.client
import json

print("=" * 70)
print("FINAL CSRF LOGIN TEST - WITH CORRECT COOKIE NAMES")
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

# Parse ALL cookies
cookies = {}
for header, value in response.getheaders():
    if header.lower() == 'set-cookie':
        parts = value.split(';')
        name_value = parts[0].split('=', 1)
        if len(name_value) == 2:
            cookies[name_value[0]] = name_value[1]
            print(f"  Cookie: {name_value[0]} = {name_value[1][:40]}...")

response.read()
conn.close()

xsrf_token = cookies.get('XSRF-TOKEN')
session_cookie = cookies.get('team_management_platform_session')

if not xsrf_token or not session_cookie:
    print("\n❌ Missing required cookies!")
    print(f"  Cookies found: {list(cookies.keys())}")
    exit(1)

print(f"\n✅ Both cookies received")

# Step 2: Login with both cookies
print("\n\n" + "=" * 70)
print("[2] Login Request with Both Cookies")
print("=" * 70)

conn = http.client.HTTPConnection("localhost", 80)

# Build cookie header with BOTH cookies
cookie_header = f"XSRF-TOKEN={xsrf_token}; team_management_platform_session={session_cookie}"
xsrf_header_value = urllib.parse.unquote(xsrf_token)

print(f"\nX-XSRF-TOKEN (decoded): {xsrf_header_value[:60]}...")
print(f"Cookie header (encoded): {cookie_header[:100]}...")

login_headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
    "Origin": "http://localhost:5173",
    "Referer": "http://localhost:5173/login",
    "X-XSRF-TOKEN": xsrf_header_value,
    "Cookie": cookie_header,
}

body = json.dumps({
    "email": "ceo@example.com",
    "password": "password123"
})

conn.request("POST", "/api/v1/auth/login", body=body, headers=login_headers)
response = conn.getresponse()

print(f"\nResponse: {response.status} {response.reason}")

response_body = response.read().decode('utf-8')

conn.close()

print("\n" + "=" * 70)
if response.status == 200:
    print("[SUCCESS] LOGIN SUCCESSFUL!")
    print("\nThe CORS/CSRF issue is RESOLVED!")
    try:
        data = json.loads(response_body)
        print(f"\nUser: {data.get('user', {}).get('name', 'N/A')}")
        print(f"Token: {data.get('token', 'N/A')[:50]}...")
    except:
        print(f"\nResponse: {response_body[:300]}")
elif response.status == 419:
    print("[FAIL] CSRF TOKEN MISMATCH (419) - Still an issue")
    print(f"\nResponse: {response_body[:300]}")
else:
    print(f"[FAIL] Status: {response.status}")
    print(f"\nResponse: {response_body[:300]}")
print("=" * 70)
