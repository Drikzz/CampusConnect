/**
 * Registration data manager
 * Handles storing and clearing registration data consistently across the application
 */

// Define registration routes - correcting to match actual route structure
export const registrationRoutes = [
    '/register',              // This matches the first step (personal info)
    '/register/details',      // This matches the second step (account details)
    '/register/profile-picture' // This matches the final step (profile picture)
];

// Check if a path is a registration route
export function isRegistrationRoute(path) {
    if (!path) return false;
    return registrationRoutes.some(route => path.startsWith(route));
}

// Clear all registration data
export function clearRegistrationData() {
    console.log("Utility: Clearing all registration data from storage");
    
    const formFields = [
        'user_type_id', 
        'grade_level_id', 
        'wmsu_dept_id', 
        'first_name', 
        'middle_name', 
        'last_name', 
        'gender', 
        'date_of_birth', 
        'phone',
        'username',
        'wmsu_email',
        'from_account_info',
        'registration_in_progress'
    ];
    
    formFields.forEach(field => {
        sessionStorage.removeItem(field);
    });
    
    return true;
}

// Load registration data into a form
export function loadRegistrationData(form, fields) {
    if (!form) return;
    
    fields.forEach(field => {
        const value = sessionStorage.getItem(field);
        if (value) {
            form[field] = value;
        }
    });
}

// Save form data to registration storage
export function saveRegistrationData(formData) {
    if (!formData) return;
    
    Object.entries(formData).forEach(([key, value]) => {
        if (value !== null && value !== undefined) {
            sessionStorage.setItem(key, value);
        }
    });
}
