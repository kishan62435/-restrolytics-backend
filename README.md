# 🍽️ RestroLytics Backend

A powerful Laravel-based REST API for restaurant analytics and order management. This backend provides comprehensive insights into restaurant performance, order trends, and revenue analytics with efficient caching and filtering capabilities.

## ✨ Features

- **📊 Restaurant Analytics**: Daily order counts, revenue tracking, and peak hour analysis
- **🔍 Advanced Filtering**: Filter by date range, amount range, hour range, and restaurant selection
- **⚡ Performance Optimized**: Built-in caching system for improved response times
- **📱 RESTful API**: Clean, well-structured API endpoints following REST principles
- **🗄️ Efficient Data Handling**: Optimized queries with pagination for large datasets
- **🔐 Input Validation**: Comprehensive request validation and error handling

## 🚀 API Endpoints

### Analytics Endpoints

#### 1. Restaurant Trends Analysis
```http
POST /api/analytics/restaurant-trends
```
**Description**: Get comprehensive analytics for restaurants including daily orders, revenue, and peak hours.

**Request Body**:
```json
{
  "restaurant_ids": [1, 2, 3],
  "from": "2024-01-01",
  "to": "2024-01-31",
  "minA": 0,
  "maxA": 1000,
  "hFrom": "09:00",
  "hTo": "22:00"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Restaurants trends fetched successfully",
  "data": [
    {
      "restaurant_id": 1,
      "restaurant_name": "Pizza Palace",
      "trends": {
        "daily": [...],
        "hourly": [...]
      }
    }
  ]
}
```

#### 2. Top Restaurants by Revenue
```http
POST /api/analytics/top-restaurants
```
**Description**: Get top 3 restaurants ranked by revenue for a given date range.

**Request Body**:
```json
{
  "restaurant_ids": [1, 2, 3],
  "from": "2024-01-01",
  "to": "2024-01-31",
  "minA": 0,
  "maxA": 1000
}
```

### Restaurant Management

#### 3. List All Restaurants
```http
GET /api/restaurants
```

#### 4. Get Restaurant Details
```http
GET /api/restaurants/{id}
```

### Order Management

#### 5. List Restaurant Orders
```http
POST /api/orders
```
**Description**: Get paginated list of orders for a specific restaurant with advanced filtering.

**Request Body**:
```json
{
  "restaurant_id": 1,
  "from": "2024-01-01",
  "to": "2024-01-31",
  "minA": 0,
  "maxA": 1000,
  "hFrom": "09:00",
  "hTo": "22:00",
  "per_page": 20
}
```

**Response**:
```json
{
  "success": true,
  "message": "Orders retrieved successfully",
  "data": [...],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100,
    "from": 1,
    "to": 20
  }
}
```

## 🛠️ Technology Stack

- **Framework**: Laravel 10.x
- **Database**: MySQL/PostgreSQL
- **Cache**: File-based caching (configurable to Redis/Memcached)
- **Validation**: Laravel Request Validation
- **API**: RESTful API with JSON responses
- **Performance**: Query optimization with eager loading

## 📋 Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL/PostgreSQL
- Web server (Apache/Nginx) or PHP built-in server

## 🚀 Installation & Setup

### 1. Clone the Repository
```bash
git clone git@github.com:kishan62435/-restrolytics-backend.git
cd -restrolytics-backend
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
```
Edit `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restrolytics
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Seed Sample Data (Optional)
```bash
php artisan db:seed
```

### 7. Start the Server
```bash
php artisan serve
```

Your API will be available at `http://localhost:8000`

## 📊 Database Schema

### Restaurants Table
- `id` - Primary key
- `name` - Restaurant name
- `location` - Restaurant location
- `cuisine` - Type of cuisine

### Orders Table
- `id` - Primary key
- `restaurant_id` - Foreign key to restaurants
- `order_amount` - Order total amount
- `order_time` - Timestamp of order

## 🔧 Configuration

### Cache Configuration
The application uses file-based caching by default. To use Redis:

1. Install Redis extension: `composer require predis/predis`
2. Update `.env`:
   ```env
   CACHE_DRIVER=redis
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

### Pagination
Default pagination is 10 items per page. Customize via `per_page` parameter (max: 100).

## 📈 Usage Examples

### Get Restaurant Trends for Last Month
```bash
curl -X POST http://localhost:8000/api/analytics/restaurant-trends \
  -H "Content-Type: application/json" \
  -d '{
    "from": "2024-01-01",
    "to": "2024-01-31",
    "minA": 10,
    "maxA": 500
  }'
```

### Get Orders for Specific Restaurant
```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "restaurant_id": 1,
    "per_page": 25
  }'
```

## 🧪 Testing

Run the test suite:
```bash
php artisan test
```

## 📝 API Documentation

For detailed API documentation, refer to the inline code comments and validation rules in the controllers.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/kishan62435/-restrolytics-backend/issues) page
2. Create a new issue with detailed description
3. Contact the maintainers

## 🎯 Roadmap

- [ ] Redis caching implementation
- [ ] API rate limiting
- [ ] Authentication & authorization
- [ ] Real-time analytics with WebSockets
- [ ] Export functionality (CSV, PDF)
- [ ] Advanced reporting dashboard
- [ ] Mobile app support

---

**Built with ❤️ using Laravel**
