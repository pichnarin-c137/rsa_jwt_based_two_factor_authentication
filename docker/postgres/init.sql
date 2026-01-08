-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Set default UUID generation
ALTER DATABASE rsa_jwt_auth SET default_with_oids = false;
