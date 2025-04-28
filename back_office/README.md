# Financial Dashboard

This is a modern financial dashboard implemented with HTML, CSS, and JavaScript using an MVC architecture. The dashboard displays financial information including balance, expenses, investments, and transactions, with support for both light and dark themes.

## Features

- **Responsive Design**: Works on various screen sizes from desktop to mobile
- **Light/Dark Theme**: Toggle between light and dark themes with persistent user preference
- **Financial Overview**: Balance, expenses, and investment tracking
- **Transaction History**: Recent transaction list
- **Visual Charts**: Graphical representation of financial data
- **MVC Architecture**: Clean separation of concerns between models, views, and controllers

## Project Structure

```
.
├── model/
│   └── dashboardModel.js   # Data models and business logic
├── view/
│   ├── dashboard.html      # Main HTML structure
│   └── styles.css          # CSS styling for the dashboard
└── controllers/
    └── dashboardController.js # Logic to connect the model and view
```

## Implementation Details

### Model

The `dashboardModel.js` file contains the data structure and operations for the financial dashboard, including:
- User information
- Financial data (balance, expenses, transactions)
- Investment information
- Data for charts and visualizations

### View

The view consists of:
- `dashboard.html`: Main markup structure for the interface
- `styles.css`: Styling for both light and dark themes, layout, and responsive design

### Controller

The `dashboardController.js` connects the model and view:
- Initializes the dashboard
- Handles theme switching
- Manages data flow between the model and view

## Getting Started

1. Clone the repository
2. Open `view/dashboard.html` in a web browser to see the dashboard

## Screenshots

The dashboard includes both light and dark themes:

- Light Theme: A clean, white-based interface
- Dark Theme: A modern, dark-based interface for reduced eye strain

## Future Enhancements

- Real-time data updates
- User authentication
- More detailed financial analytics
- Additional chart visualizations
- Transaction filtering and searching 