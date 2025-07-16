# Library Management System
 Library online is a PHP-based library management system for tracking books, authors, genres, and user transactions. It provides CRUD operations for books, authors, and genres, as well as borrowing and returning workflows. The UI is styled for clarity and usability, with filtering and pagination features.

## Techniques Used

- **Prepared Statements**: All database queries use [MySQLi prepared statements](https://www.php.net/manual/en/mysqli.prepare.php) for security and performance.
- **Dynamic Filtering**: Book lists support multi-criteria filtering (author, genre, year, status) using dynamic SQL query construction.
- **AJAX-like UI Filtering**: The year range filter uses [noUiSlider](https://refreshless.com/nouislider/) for interactive selection, updating hidden form fields for server-side filtering.
- **Pagination**: Transaction history uses server-side pagination for scalable data browsing.
- **Status Highlighting**: CSS classes visually distinguish book and transaction statuses.
- **Date Calculations**: Borrowed book deadlines are calculated using SQL’s [DATEDIFF](https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_datediff) and PHP’s [date](https://www.php.net/manual/en/function.date.php) functions.
- **HTML/CSS Styling**: Uses [CSS Flexbox](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Flexible_Box_Layout/Basic_Concepts_of_Flexbox) for responsive filter forms and tables.

## Technologies and Libraries

- **PHP**: Backend logic and database access.
- **MySQL/MariaDB**: Relational database for persistent storage.
- **noUiSlider**: [noUiSlider](https://refreshless.com/nouislider/) for interactive year range selection.
- **HTML5/CSS3**: Semantic markup and modern styling.
- **phpMyAdmin**: SQL dump format for database schema (database.sql).

## Project Structure

```
/
├── add_book.php
├── authors.php
├── borrow.php
├── db.php
├── genre.php
├── index.html
├── lista_ksiazek.php
├── README.md
├── style.css
├── transactions.php
├── user_borrow.php
├── zwroc.php
├── database.sql
```

- All files are in the root directory.
- Images or assets directories are not present, but external CSS and JS libraries are loaded via CDN.
- style.css contains all UI styling.
- database.sql provides the schema for initial setup.

## External Libraries

- [noUiSlider](https://refreshless.com/nouislider/) (CDN: [cdnjs](https://cdnjs.com/libraries/noUiSlider))
- [CSS Flexbox](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Flexible_Box_Layout/Basic_Concepts_of_Flexbox) for layout
