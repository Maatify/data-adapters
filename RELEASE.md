## **Release Title**

**⭐ v1.1.1 — MongoAdapter Helper Methods (getClient + getDatabase)**

---

## **Release Notes**

```
This patch release introduces two highly requested helper methods:

• getClient() — direct access to MongoDB\Client  
• getDatabase() — profile-aware database resolver

These additions make the Mongo adapter consistent with the MySQL/Redis helper
interfaces while maintaining full compatibility with the Phase 13 unified
configuration engine.

No breaking changes. All tests passing.
```

---