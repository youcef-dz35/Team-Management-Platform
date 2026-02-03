import requests

session = requests.Session()

print("Testing CSRF cookie endpoint...")
print("=" * 60)

# Test /sanctum/csrf-cookie
response = session.get(
    "http://localhost/sanctum/csrf-cookie",
    headers={
        "Accept": "application/json",
        "Origin": "http://localhost:5173",
        "Referer": "http://localhost:5173/",
    }
)

print(f"Status: {response.status_code}")
print(f"\nResponse Headers:")
for header, value in response.headers.items():
    print(f"  {header}: {value}")

print(f"\nCookies received:")
for cookie in session.cookies:
    print(f"  {cookie.name}: {cookie.value[:50]}...")

print(f"\nXSRF-TOKEN cookie present: {'XSRF-TOKEN' in session.cookies}")
print(f"Session cookie present: {'team_management_platform_session' in session.cookies}")

# Try extracting XSRF token
xsrf_token = session.cookies.get('XSRF-TOKEN', '')
print(f"\nXSRF Token (first 50 chars): {xsrf_token[:50]}")
