#!/usr/bin/env python3
"""
Direct cURL-style test to check exact headers and cookies
"""
import http.client
import urllib.parse

# Test 1: CSRF Cookie Request
print("=" * 70)
print("TEST 1: Fetching CSRF Cookie")
print("=" * 70)

conn = http.client.HTTPConnection("localhost", 80)

headers = {
    "Accept": "application/json",
    "Origin": "http://localhost:5173",
    "Referer": "http://localhost:5173/",
}

conn.request("GET", "/sanctum/csrf-cookie", headers=headers)
response = conn.getresponse()

print(f"Status: {response.status} {response.reason}")
print(f"\nResponse Headers:")
for header, value in response.getheaders():
    print(f"  {header}: {value}")

# Extract Set-Cookie headers
cookies_raw = response.getheader('Set-Cookie', '')
print(f"\nRaw Set-Cookie: {cookies_raw[:200]}")

# Parse cookies
cookie_jar = {}
for header, value in response.getheaders():
    if header.lower() == 'set-cookie':
        # Extract cookie name and value
        cookie_parts = value.split(';')
        if cookie_parts:
            name_value = cookie_parts[0].split('=', 1)
            if len(name_value) == 2:
                cookie_jar[name_value[0]] = name_value[1]
                print(f"\n  Cookie: {name_value[0]}")
                print(f"  Full: {value}")

xsrf_token = cookie_jar.get('XSRF-TOKEN', '')
laravel_session = cookie_jar.get('laravel_session', '')

print(f"\n\nExtracted tokens:")
print(f"  XSRF-TOKEN: {xsrf_token[:50] if xsrf_token else 'NOT SET'}...")
print(f"  laravel_session: {laravel_session[:50] if laravel_session else 'NOT SET'}...")

response.read()  # Consume response
conn.close()

# Test 2: Login Request
if not xsrf_token:
    print("\n❌ No XSRF token received - cannot test login")
    exit(1)

print("\n\n" + "=" * 70)
print("TEST 2: Login Request with CSRF Token")
print("=" * 70)

conn = http.client.HTTPConnection("localhost", 80)

# Build cookie header
cookie_header = f"XSRF-TOKEN={xsrf_token}"
if laravel_session:
    cookie_header += f"; laravel_session={laravel_session}"

login_headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
    "Origin": "http://localhost:5173",
    "Referer": "http://localhost:5173/login",
    "X-XSRF-TOKEN": xsrf_token,
    "Cookie": cookie_header,
}

body = '{"email":"ceo@example.com","password":"password123"}'

print(f"\nRequest Headers:")
for key, value in login_headers.items():
    if "TOKEN" in key or "Cookie" in key:
        print(f"  {key}: {value[:60]}...")
    else:
        print(f"  {key}: {value}")

conn.request("POST", "/api/v1/auth/login", body=body, headers=login_headers)
response = conn.getresponse()

print(f"\nStatus: {response.status} {response.reason}")
print(f"\nResponse Headers:")
for header, value in response.getheaders():
    print(f"  {header}: {value[:100]}")

response_body = response.read().decode('utf-8')
print(f"\nResponse Body:")
print(response_body[:500])

conn.close()

print("\n" + "=" * 70)
if response.status == 200:
    print("✅ LOGIN SUCCESSFUL")
elif response.status == 419:
    print("❌ CSRF TOKEN MISMATCH (419)")
else:
    print(f"❌ UNEXPECTED STATUS: {response.status}")
print("=" * 70)
