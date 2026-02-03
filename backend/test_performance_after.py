import time
import http.client
import urllib.parse

# Test multiple endpoints to get average
endpoints = [
    ("/sanctum/csrf-cookie", "GET"),
    ("/api/v1/auth/login", "POST"),
]

print("=" * 60)
print("BACKEND PERFORMANCE TEST")
print("=" * 60)

# Get CSRF first
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

# Test login endpoint with timing
print("\n[TEST 1] POST /api/v1/auth/login")
cookie_hdr = "; ".join([f"{k}={v}" for k, v in cookies.items()])
xsrf = urllib.parse.unquote(cookies.get('XSRF-TOKEN', ''))

start = time.time()
conn = http.client.HTTPConnection("localhost", 80)
conn.request("POST", "/api/v1/auth/login",
    body='{"email":"ceo@example.com","password":"password123"}',
    headers={
        "Content-Type": "application/json",
        "Accept": "application/json",
        "Origin": "http://localhost:5173",
        "X-XSRF-TOKEN": xsrf,
        "Cookie": cookie_hdr,
    })

response = conn.getresponse()
elapsed_ms = (time.time() - start) * 1000
response_body = response.read().decode()
conn.close()

print(f"  Status: {response.status}")
print(f"  Response time: {elapsed_ms:.0f}ms")

if elapsed_ms < 300:
    print(f"  ✓ EXCELLENT - Under 300ms target (was ~1650ms)")
elif elapsed_ms < 500:
    print(f"  ✓ GOOD - Significant improvement from 1650ms")
elif elapsed_ms < 1000:
    print(f"  ~ MODERATE - Some improvement but still slow")
else:
    print(f"  ✗ SLOW - Still over 1 second")

print(f"\n  Performance Improvement: {((1650 - elapsed_ms) / 1650 * 100):.1f}%")
print(f"  Time saved: {(1650 - elapsed_ms):.0f}ms per request")

print("\n" + "=" * 60)
print("SUMMARY")
print("=" * 60)
print(f"  Before: ~1650ms")
print(f"  After:  {elapsed_ms:.0f}ms")
print(f"  Improvement: {((1650 - elapsed_ms) / 1650 * 100):.1f}%")
if elapsed_ms < 300:
    print("  Status: ✓ TARGET MET - <300ms")
else:
    print(f"  Status: ~ PROGRESS - Still {elapsed_ms:.0f}ms")
