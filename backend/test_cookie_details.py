#!/usr/bin/env python3
import requests

session = requests.Session()

print("Testing CSRF Cookie Details")
print("=" * 70)

# Get CSRF cookie
response = session.get(
    "http://localhost/sanctum/csrf-cookie",
    headers={
        "Accept": "application/json",
        "Origin": "http://localhost:5173",
        "Referer": "http://localhost:5173/",
    }
)

print(f"Status: {response.status_code}\n")

# Examine all cookies
print("Cookies in jar:")
for cookie in session.cookies:
    print(f"\n  Name: {cookie.name}")
    print(f"  Value: {cookie.value[:50]}...")
    print(f"  Domain: {cookie.domain}")
    print(f"  Path: {cookie.path}")
    print(f"  Secure: {cookie.secure}")
    print(f"  SameSite: {cookie.get_nonstandard_attr('SameSite', 'not set')}")
    print(f"  Expires: {cookie.expires}")

# Check Set-Cookie headers
print(f"\n\nSet-Cookie Headers:")
for header, value in response.headers.items():
    if header.lower() == 'set-cookie':
        print(f"  {value}")

# Test if cookies will be sent
print(f"\n\nCookies that will be sent to localhost:")
print(f"  {session.cookies.get_dict()}")
