/**
 * Core.gs
 * Utilitas inti untuk keamanan, validasi, dan standardisasi respons API.
 */

// --- 1. RESPONSE FORMAT ---
const ResponseFormat = {
  success: function(data = null, message = 'Success') {
    return { status: 'success', message: message, data: data };
  },
  error: function(message = 'An error occurred') {
    return { status: 'error', message: message };
  },
  validationError: function(errors = []) {
    return { status: 'validation_error', message: 'Validation failed', errors: errors };
  },
  unauthorized: function(message = 'Unauthorized') {
    return { status: 'unauthorized', message: message };
  },
  forbidden: function(message = 'Forbidden') {
    return { status: 'forbidden', message: message };
  },
  notFound: function(message = 'Not Found') {
    return { status: 'not_found', message: message };
  }
};

// --- 2. SESSION MANAGER ---
const SessionManager = {
  _properties: PropertiesService.getScriptProperties(),
  
  createSession: function(user) {
    const token = Utilities.getUuid();
    const sessionData = {
      id: user.id,
      role: user.role,
      name: user.name,
      username: user.username,
      expires_at: new Date().getTime() + (2 * 60 * 60 * 1000) // 2 jam expiry
    };
    this._properties.setProperty('session_' + token, JSON.stringify(sessionData));
    return token;
  },
  
  getUser: function(token) {
    if (!token) return null;
    const sessionString = this._properties.getProperty('session_' + token);
    if (!sessionString) return null;
    
    const sessionData = JSON.parse(sessionString);
    if (new Date().getTime() > sessionData.expires_at) {
      this.destroySession(token);
      return null;
    }
    return sessionData;
  },
  
  destroySession: function(token) {
    if (token) this._properties.deleteProperty('session_' + token);
  },
  
  // Guard Clauses (Middleware Emulation)
  requireAuth: function(token) {
    const user = this.getUser(token);
    if (!user) throw new Error('unauthorized');
    return user;
  },
  
  requireRole: function(token, role) {
    const user = this.requireAuth(token);
    if (user.role !== role) throw new Error('forbidden');
    return user;
  }
};

// --- 3. FORM VALIDATOR ---
const FormValidator = {
  validate: function(data, rules) {
    let errors = {};
    
    for (const [field, ruleString] of Object.entries(rules)) {
      const fieldRules = ruleString.split('|');
      const value = data[field];
      
      for (const rule of fieldRules) {
        // Required
        if (rule === 'required') {
          if (value === undefined || value === null || String(value).trim() === '') {
            if (!errors[field]) errors[field] = [];
            errors[field].push(`The ${field} field is required.`);
          }
        }
        
        // Skip further validation if empty and not required
        if (value === undefined || value === null || String(value).trim() === '') continue;
        
        // Numeric
        if (rule === 'numeric') {
          if (isNaN(value)) {
            if (!errors[field]) errors[field] = [];
            errors[field].push(`The ${field} must be a number.`);
          }
        }
        
        // Max Length or Value
        if (rule.startsWith('max:')) {
          const max = parseInt(rule.split(':')[1]);
          const isNumeric = fieldRules.includes('numeric');
          if (isNumeric) {
            if (parseFloat(value) > max) {
              if (!errors[field]) errors[field] = [];
              errors[field].push(`The ${field} may not be greater than ${max}.`);
            }
          } else {
            if (String(value).length > max) {
              if (!errors[field]) errors[field] = [];
              errors[field].push(`The ${field} may not be greater than ${max} characters.`);
            }
          }
        }
        
        // Min Length or Value
        if (rule.startsWith('min:')) {
          const min = parseInt(rule.split(':')[1]);
          const isNumeric = fieldRules.includes('numeric');
          if (isNumeric) {
            if (parseFloat(value) < min) {
              if (!errors[field]) errors[field] = [];
              errors[field].push(`The ${field} must be at least ${min}.`);
            }
          } else {
            if (String(value).length < min) {
              if (!errors[field]) errors[field] = [];
              errors[field].push(`The ${field} must be at least ${min} characters.`);
            }
          }
        }
        
        // Unique:table,column
        if (rule.startsWith('unique:')) {
          const params = rule.split(':')[1].split(',');
          const table = params[0];
          const column = params[1] || field;
          const exceptId = params[2]; // misal saat update
          
          let query = Database.table(table).where(column, '=', value);
          if (exceptId) {
            query = query.where('id', '!=', exceptId);
          }
          if (query.exists()) {
            if (!errors[field]) errors[field] = [];
            errors[field].push(`The ${field} has already been taken.`);
          }
        }
        
        // Exists:table,column
        if (rule.startsWith('exists:')) {
          const params = rule.split(':')[1].split(',');
          const table = params[0];
          const column = params[1] || 'id';
          
          if (!Database.table(table).where(column, '=', value).exists()) {
            if (!errors[field]) errors[field] = [];
            errors[field].push(`The selected ${field} is invalid.`);
          }
        }
        
        // Enum:a,b,c
        if (rule.startsWith('in:')) {
          const allowed = rule.split(':')[1].split(',');
          if (!allowed.includes(String(value))) {
            if (!errors[field]) errors[field] = [];
            errors[field].push(`The selected ${field} is invalid.`);
          }
        }
      }
    }
    
    return Object.keys(errors).length > 0 ? errors : null;
  }
};
