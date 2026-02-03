#!/usr/bin/env python3
"""Check what cookies are actually being set by /sanctum/csrf-cookie"""
import http.client

conn = http.client.HTTPConnection("localhost", 80)

headers = {
    "Accept": "application/json",
    "Origin": "http://localhost:5173",
    "Referer": "http://localhost:5173/",
}

conn.request("GET", "/sanctum/csrf-cookie", headers=headers)
response = conn.getresponse()

print("=" * 70)
print("SANCTUM CSRF COOKIE RESPONSE")
print("=" * 70)
print(f"\nStatus: {response.status} {response.reason}\n")

print("ALL Response Headers:")
print("-" * 70)
for header, value in response.getheaders():
    print(f"{header}: {value[:100]}")

print("\n" + "=" * 70)
print("SET-COOKIE HEADERS ONLY:")
print("=" * 70)

cookie_count = 0
for header, value in response.getheaders():
    if header.lower() == 'set-cookie':
        cookie_count += 1
        print(f"\n[Cookie #{cookie_count}]")
        print(f"  Full: {value}")
        
        # Parse cookie name
        parts = value.split(';')
        if parts:
            name_value = parts[0].split('=', 1)
            if len(name_value) == 2:
                print(f"  Name: {name_value[0]}")
                print(f"  Value: {name_value[1][:50]}...")
                
                # Parse attributes
                for part in parts[1:]:
                    part = part.strip()
                    print(f"  Attr: {part}")

print(f"\n\nTotal cookies set: {cookie_count}")

if cookie_count == 0:
    print("\n❌ NO COOKIES BEING SET!")
elif cookie_count == 1:
    print("\n⚠️  Only 1 cookie (expected 2: XSRF-TOKEN + laravel_session)")
else:
    print(f"\n✅ {cookie_count} cookies set")

response.read()
conn.close()
