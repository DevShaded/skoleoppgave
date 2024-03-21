
CREATE TABLE IF NOT EXISTS Users
(
    user_id
    INT
    AUTO_INCREMENT
    PRIMARY
    KEY,
    username
    VARCHAR
(
    50
) UNIQUE,
    password VARCHAR
(
    100
),
    name VARCHAR
(
    100
),
    email VARCHAR
(
    100
),
    phone VARCHAR
(
    20
)
    );


CREATE TABLE IF NOT EXISTS UserInformations
(
    user_info_id
    INT
    AUTO_INCREMENT
    PRIMARY
    KEY,
    user_id
    INT
    NOT
    NULL,
    FOREIGN
    KEY
(
    user_id
)
    REFERENCES Users
(
    user_id
),
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    location VARCHAR(255)
    );
