/**
 * Dashboard Model
 * Contains data structures and operations for the financial dashboard
 */

class DashboardModel {
    constructor() {
        this.userData = {
            name: 'Kristi Kamylova',
            balance: 857850,
            expenses: 198110,
            balanceChange: {
                amount: 12484,
                percentage: 12,
                isPositive: true
            },
            expensesChange: {
                amount: 12648,
                percentage: 6,
                isPositive: false
            }
        };
        
        this.categories = [
            { name: 'Food', percentage: 57, color: 'var(--color-food)' },
            { name: 'House', percentage: 76, color: 'var(--color-house)' },
            { name: 'Car', percentage: 21, color: 'var(--color-car)' },
            { name: 'Party', percentage: 34, color: 'var(--color-party)' },
            { name: 'Holiday', percentage: 10, color: 'var(--color-holiday)' }
        ];
        
        this.investments = [
            { name: 'Shares', amount: 200.00, percentage: 60 },
            { name: 'Cryptocurrency', amount: 220.00, percentage: 75 },
            { name: 'Contributions', amount: 135.00, percentage: 35 },
            { name: 'Stocks and bonds', amount: 450.00, percentage: 80 },
            { name: 'Assets', amount: 577.00, percentage: 90 }
        ];
        
        this.translations = [
            { name: 'Shuttle', date: 'May 01, 17:45', amount: -23.25, icon: '🛒' },
            { name: 'Boutique', date: 'May 01, 16:50', amount: -10.78, icon: '🛍️' },
            { name: 'Gym', date: 'May 01, 12:30', amount: -30.12, icon: '🏪' },
            { name: 'Restaurant', date: 'May 01, 9:50', amount: -12.35, icon: '🍽️' }
        ];
        
        this.cards = [
            { type: 'Products', balance: 142.23, number: '3745 **** **** 4478', brand: 'mastercard' },
            { type: 'VISA', balance: 142.23, number: '3745 **** **** 4478', brand: 'visa' }
        ];
        
        // Weekly data for charts
        this.weeklyData = {
            income: [1200, 1500, 1800, 1400, 1600, 1300],
            expenses: [800, 1200, 900, 1100, 1300, 700]
        };
        
        // Monthly data for charts
        this.monthlyData = {
            income: Array.from({ length: 30 }, () => Math.floor(Math.random() * 2000) + 500),
            expenses: Array.from({ length: 30 }, () => Math.floor(Math.random() * 1500) + 300)
        };
    }
    
    // Get user information
    getUserData() {
        return this.userData;
    }
    
    // Get spending categories
    getCategories() {
        return this.categories;
    }
    
    // Get investment information
    getInvestments() {
        return this.investments;
    }
    
    // Get recent translations/transactions
    getTranslations(limit = null) {
        if (limit) {
            return this.translations.slice(0, limit);
        }
        return this.translations;
    }
    
    // Get active cards
    getCards() {
        return this.cards;
    }
    
    // Get weekly income/expenses data for charts
    getWeeklyData() {
        return this.weeklyData;
    }
    
    // Get monthly income/expenses data for charts
    getMonthlyData() {
        return this.monthlyData;
    }
    
    // Calculate total investments
    getTotalInvestments() {
        return this.investments.reduce((total, investment) => total + investment.amount, 0);
    }
    
    // Calculate total spending by category
    getTotalSpendingByCategory(categoryName) {
        const category = this.categories.find(cat => cat.name === categoryName);
        if (!category) return 0;
        
        // This is a simplified calculation based on the percentage
        return (category.percentage / 100) * this.userData.expenses;
    }
    
    // Update user balance
    updateBalance(amount) {
        const oldBalance = this.userData.balance;
        this.userData.balance += amount;
        
        // Calculate the change percentage
        const changeAmount = this.userData.balance - oldBalance;
        const changePercentage = Math.abs((changeAmount / oldBalance) * 100).toFixed(0);
        
        this.userData.balanceChange = {
            amount: Math.abs(changeAmount),
            percentage: changePercentage,
            isPositive: changeAmount >= 0
        };
        
        return this.userData.balance;
    }
    
    // Add a new transaction
    addTransaction(name, amount, icon = '💰') {
        const now = new Date();
        const formattedDate = `${now.toLocaleString('default', { month: 'short' })} ${now.getDate()}, ${now.getHours()}:${now.getMinutes()}`;
        
        const newTransaction = {
            name,
            date: formattedDate,
            amount,
            icon
        };
        
        this.translations.unshift(newTransaction);
        
        // Update balance based on transaction
        this.updateBalance(amount);
        
        return newTransaction;
    }
}

// Export the model
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DashboardModel;
} else {
    // For browser usage
    window.DashboardModel = DashboardModel;
} 