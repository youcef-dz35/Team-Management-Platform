import http.client

# Get CSRF cookie
conn = http.client.HTTPConnection("localhost", 80)
conn.request("GET", "/sanctum/csrf-cookie", headers={"Origin": "http://localhost:5173"})
response = conn.getresponse()

cookies = {}
for h, v in response.getheaders():
    if h.lower() == 'set-cookie':
        name, val = v.split(';')[0].split('=', 1)
        cookies[name] = val

response.read()
conn.close()

# Check CORS headers on actual endpoint
import urllib.parse
conn = http.client.HTTPConnection("localhost", 80)
cookie_hdr = "; ".join([f"{k}={v}" for k, v in cookies.items()])
xsrf = urllib.parse.unquote(cookies.get('XSRF-TOKEN', ''))

conn.request("POST", "/api/v1/auth/login",
    body='{"email":"ceo@example.com","password":"password123"}',
    headers={
        "Content-Type": "application/json",
        "Accept": "application/json",
        "Origin": "http://localhost:5173",
        "Referer": "http://localhost:5173/",
        "X-XSRF-TOKEN": xsrf,
        "Cookie": cookie_hdr,
    })

response = conn.getresponse()

print(f"Status: {response.status}")
print(f"\nCORS-related response headers:")
for h, v in response.getheaders():
    if 'access' in h.lower() or 'origin' in h.lower():
        print(f"  {h}: {v}")

print(f"\nAll headers (first 10):")
for i, (h, v) in enumerate(response.getheaders()[:10]):
    print(f"  {h}: {v[:80]}")

body = response.read().decode()
print(f"\nBody preview: {body[:200]}")

conn.close()
