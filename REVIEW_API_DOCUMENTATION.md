# Review API Documentation

## Overview

API untuk mengelola review film dengan autentikasi Sanctum. User dapat membuat, melihat, mengupdate, dan menghapus review mereka sendiri.

## Authentication

Semua endpoint yang memerlukan autentikasi harus menyertakan token Bearer di header:

```
Authorization: Bearer {your-token}
```

---

## Endpoints

### 1. Create Review (Protected)

**POST** `/api/reviews`

Membuat review baru untuk sebuah film. User hanya bisa membuat satu review per film.

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "id_movie": "12345",
    "rating": 8,
    "review": "Film yang sangat bagus! Ceritanya menarik dan akting para pemainnya luar biasa."
}
```

**Validation Rules:**

-   `id_movie`: required, string
-   `rating`: required, integer, min: 1, max: 10
-   `review`: required, string, min: 10 characters, max: 1000 characters

**Success Response (201):**

```json
{
    "message": "Review created successfully",
    "data": {
        "id_review": "uuid-here",
        "id_user": "user-uuid",
        "user": {
            "id_user": "user-uuid",
            "name": "John Doe"
        },
        "id_movie": "12345",
        "rating": 8,
        "review": "Film yang sangat bagus!...",
        "created_at": "2026-01-16T23:00:00.000000Z"
    }
}
```

**Error Response (409):**

```json
{
    "message": "You have already reviewed this movie"
}
```

---

### 2. Get Reviews by Movie (Public)

**GET** `/api/reviews/movie/{movieId}`

Mendapatkan semua review untuk film tertentu.

**Parameters:**

-   `movieId` (path): ID film

**Success Response (200):**

```json
{
    "message": "Reviews retrieved successfully",
    "data": [
        {
            "id_review": "uuid-1",
            "id_user": "user-uuid-1",
            "user": {
                "id_user": "user-uuid-1",
                "name": "John Doe"
            },
            "id_movie": "12345",
            "rating": 8,
            "review": "Great movie!",
            "created_at": "2026-01-16T23:00:00.000000Z"
        }
    ]
}
```

---

### 3. Get Single Review (Public)

**GET** `/api/reviews/{id}`

Mendapatkan detail satu review berdasarkan ID.

**Parameters:**

-   `id` (path): ID review

**Success Response (200):**

```json
{
    "message": "Review retrieved successfully",
    "data": {
        "id_review": "uuid-here",
        "id_user": "user-uuid",
        "user": {
            "id_user": "user-uuid",
            "name": "John Doe"
        },
        "id_movie": "12345",
        "rating": 8,
        "review": "Great movie!",
        "created_at": "2026-01-16T23:00:00.000000Z"
    }
}
```

**Error Response (404):**

```json
{
    "message": "Review not found",
    "error": "..."
}
```

---

### 4. Update Review (Protected)

**PUT** `/api/reviews/{id}`

Update review yang sudah ada. User hanya bisa update review mereka sendiri.

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Parameters:**

-   `id` (path): ID review

**Request Body:**

```json
{
    "id_movie": "12345",
    "rating": 9,
    "review": "Setelah nonton lagi, film ini lebih bagus dari yang saya kira!"
}
```

**Success Response (200):**

```json
{
    "message": "Review updated successfully",
    "data": {
        "id_review": "uuid-here",
        "id_user": "user-uuid",
        "user": {
            "id_user": "user-uuid",
            "name": "John Doe"
        },
        "id_movie": "12345",
        "rating": 9,
        "review": "Setelah nonton lagi...",
        "created_at": "2026-01-16T23:00:00.000000Z"
    }
}
```

**Error Response (403):**

```json
{
    "message": "Unauthorized to update this review"
}
```

---

### 5. Delete Review (Protected)

**DELETE** `/api/reviews/{id}`

Hapus review. User hanya bisa hapus review mereka sendiri.

**Headers:**

```
Authorization: Bearer {token}
```

**Parameters:**

-   `id` (path): ID review

**Success Response (200):**

```json
{
    "message": "Review deleted successfully"
}
```

**Error Response (403):**

```json
{
    "message": "Unauthorized to delete this review"
}
```

---

### 6. Get My Reviews (Protected)

**GET** `/api/my-reviews`

Mendapatkan semua review yang dibuat oleh user yang sedang login.

**Headers:**

```
Authorization: Bearer {token}
```

**Success Response (200):**

```json
{
    "message": "Your reviews retrieved successfully",
    "data": [
        {
            "id_review": "uuid-1",
            "id_user": "user-uuid",
            "user": {
                "id_user": "user-uuid",
                "name": "John Doe"
            },
            "id_movie": "12345",
            "rating": 8,
            "review": "Great movie!",
            "created_at": "2026-01-16T23:00:00.000000Z"
        }
    ]
}
```

---

## Error Responses

### Validation Error (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "rating": ["The rating must be between 1 and 10."],
        "review": ["The review must be at least 10 characters."]
    }
}
```

### Unauthenticated (401)

```json
{
    "message": "Unauthenticated."
}
```

### Server Error (500)

```json
{
    "message": "Failed to add review",
    "error": "Error details..."
}
```

---

## Testing Examples

### Using cURL

**Create Review:**

```bash
curl -X POST http://localhost:8000/api/reviews \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -d '{
    "id_movie": "12345",
    "rating": 8,
    "review": "Film yang sangat bagus dengan cerita menarik!"
  }'
```

**Get Reviews by Movie:**

```bash
curl -X GET http://localhost:8000/api/reviews/movie/12345
```

**Update Review:**

```bash
curl -X PUT http://localhost:8000/api/reviews/{review-id} \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -d '{
    "id_movie": "12345",
    "rating": 9,
    "review": "Review yang sudah diupdate!"
  }'
```

**Delete Review:**

```bash
curl -X DELETE http://localhost:8000/api/reviews/{review-id} \
  -H "Authorization: Bearer your-token-here"
```

**Get My Reviews:**

```bash
curl -X GET http://localhost:8000/api/my-reviews \
  -H "Authorization: Bearer your-token-here"
```

---

## Notes

1. **Rating Scale**: Rating menggunakan skala 1-10
2. **Review Length**: Review minimal 10 karakter, maksimal 1000 karakter
3. **One Review Per Movie**: Setiap user hanya bisa membuat satu review per film
4. **Authorization**: User hanya bisa update/delete review mereka sendiri
5. **Public Access**: Semua orang bisa melihat review tanpa login
6. **Protected Actions**: Membuat, update, dan delete review memerlukan autentikasi
