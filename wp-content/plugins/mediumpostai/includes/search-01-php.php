<?php

function fox_create_matterport_listings_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'matterport_model_listings6';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        model_id varchar(255) NOT NULL,
        title varchar(255) NOT NULL,
        description text NOT NULL,
        style varchar(100) NOT NULL,
        size int NOT NULL,
        features text NOT NULL,
        cost decimal(12,2) NOT NULL,
        img varchar(255) NOT NULL,
        floor_img_link varchar(255) NOT NULL,
        bedrooms int DEFAULT 3,
        bathrooms decimal(3,1) DEFAULT 2.0,
        stories int DEFAULT 1,
        garage_spaces int DEFAULT 2,
        kitchen_type varchar(50) DEFAULT 'standard',
        additional_rooms text DEFAULT '',
        flooring_type varchar(50) DEFAULT 'engineered',
        countertops_type varchar(50) DEFAULT 'quartz',
        cabinets_type varchar(50) DEFAULT 'standard',
        appliances_type varchar(50) DEFAULT 'standard',
        bath_fixtures_type varchar(50) DEFAULT 'standard',
        is_renovation tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY model_id (model_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    if ($count == 0) {
        $default_data = [
            [
                'model_id' => 'zyDSyRXxMH8',
                'title' => 'Modern Suburban Home',
                'description' => 'A sleek, open-concept home with clean lines and large windows, perfect for contemporary living.',
                'style' => 'Modern',
                'size' => 2000,
                'features' => '["Open Concept", "Smart Home", "Large Backyard"]',
                'cost' => 420000,
                'img' => 'https://webdevelopermurad.github.io/Fox-Certified-Contractors/assets/img/popular/popular-1-3.jpg',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Modern-Suburban-Home.png',
                'bedrooms' => 3,
                'bathrooms' => 2.5,
                'stories' => 1,
                'garage_spaces' => 2,
                'kitchen_type' => 'standard',
                'additional_rooms' => '["Office", "Mudroom"]',
                'flooring_type' => 'engineered',
                'countertops_type' => 'quartz'
            ],
            [
                'model_id' => 'XbSMcFdai3G',
                'title' => 'Kierland Penthouse',
                'description' => 'High-end penthouse with premium finishes and stunning city views, ideal for luxury urban dwellers.',
                'style' => 'Luxury',
                'size' => 1406,
                'features' => '["Smart Home", "Balcony", "City View"]',
                'cost' => 650000,
                'img' => 'https://webdevelopermurad.github.io/Fox-Certified-Contractors/assets/img/popular/popular-1-6.jpg',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/floor-plan-1.jpg',
                'bedrooms' => 2,
                'bathrooms' => 2.0,
                'stories' => 1,
                'garage_spaces' => 1,
                'kitchen_type' => 'gourmet',
                'flooring_type' => 'hardwood',
                'countertops_type' => 'marble',
                'cabinets_type' => 'premium',
                'appliances_type' => 'premium',
                'bath_fixtures_type' => 'premium'
            ],
            [
                'model_id' => 'vNtptZXMm8U',
                'title' => 'Classic Family Residence',
                'description' => 'A cozy family home featuring traditional architecture and spacious rooms, offering timeless appeal.',
                'style' => 'Traditional',
                'size' => 2500,
                'features' => '["Fireplace", "Large Porch", "Two-Car Garage"]',
                'cost' => 480000,
                'img' => 'https://placehold.co/400x300?text=Classic+Home',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Classic-Family-Residence.png',
                'bedrooms' => 4,
                'bathrooms' => 2.5,
                'stories' => 2,
                'garage_spaces' => 2,
                'kitchen_type' => 'standard',
                'additional_rooms' => '["Mudroom", "Pantry"]',
                'flooring_type' => 'hardwood'
            ],
            [
                'model_id' => 'P9cgFFtBnqC',
                'title' => 'Contemporary Home',
                'description' => 'Minimalist design with neutral tones and modern amenities, reflecting current architectural trends.',
                'style' => 'Contemporary',
                'size' => 1800,
                'features' => '["Smart Home", "Energy Efficient", "Minimalist Design"]',
                'cost' => 390000,
                'img' => 'https://placehold.co/400x300?text=Contemporary+Home',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Contemporary-Home-2.png',
                'bedrooms' => 3,
                'bathrooms' => 2.0,
                'stories' => 1,
                'garage_spaces' => 2,
                'kitchen_type' => 'standard',
                'flooring_type' => 'engineered'
            ],
            [
                'model_id' => 'snYJsNLhoKV',
                'title' => 'Farmhouse Retreat',
                'description' => 'A charming rustic farmhouse with warm wood accents and an inviting large porch, perfect for country living.',
                'style' => 'Farmhouse',
                'size' => 3000,
                'features' => '["Large Porch", "Fireplace", "Spacious Land"]',
                'cost' => 580000,
                'img' => 'https://placehold.co/400x300?text=Farmhouse+Retreat',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Farmhouse-Retreat.png',
                'bedrooms' => 4,
                'bathrooms' => 3.0,
                'stories' => 2,
                'garage_spaces' => 3,
                'kitchen_type' => 'gourmet',
                'additional_rooms' => '["Mudroom", "Pantry", "Laundry"]',
                'flooring_type' => 'hardwood'
            ],
            [
                'model_id' => 'BUUXxABVo6j',
                'title' => 'Black & White Modern',
                'description' => 'A monochrome-themed home with bold contrasts and striking modern design elements.',
                'style' => 'Modern',
                'size' => 2200,
                'features' => '["Open Concept", "Modern Kitchen"]',
                'cost' => 440000,
                'img' => 'https://placehold.co/400x300?text=Black+White+Modern',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Black-White-Modern.png',
                'bedrooms' => 3,
                'bathrooms' => 2.5,
                'stories' => 1,
                'garage_spaces' => 2,
                'kitchen_type' => 'standard',
                'flooring_type' => 'engineered'
            ],
            [
                'model_id' => 'xD6kzppFyoc',
                'title' => 'Prospect Urban Unit',
                'description' => 'An upscale urban apartment with high ceilings and luxurious finishes, designed for city life.',
                'style' => 'Luxury',
                'size' => 1500,
                'features' => '["Open Concept", "Urban Living", "High Ceilings"]',
                'cost' => 520000,
                'img' => 'https://placehold.co/400x300?text=Urban+Unit',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Prospect-Urban-Unit.png',
                'bedrooms' => 2,
                'bathrooms' => 2.0,
                'stories' => 1,
                'garage_spaces' => 1,
                'kitchen_type' => 'gourmet'
            ],
            [
                'model_id' => 'vaTSU2dbcN6',
                'title' => 'Pier View College Home',
                'description' => 'A student-friendly home near campus with a modern layout and communal spaces.',
                'style' => 'Contemporary',
                'size' => 2000,
                'features' => '["Open Concept", "Near University"]',
                'cost' => 410000,
                'img' => 'https://placehold.co/400x300?text=College+Home',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Pier-View-College-Home.png',
                'bedrooms' => 4,
                'bathrooms' => 2.0,
                'stories' => 2,
                'garage_spaces' => 2,
                'kitchen_type' => 'standard'
            ],
            [
                'model_id' => 'ggTGphzbRum',
                'title' => 'Jaspal Large Apartment',
                'description' => 'A spacious urban apartment with balcony and situated in a vibrant city center.',
                'style' => 'Apartment',
                'size' => 3000,
                'features' => '["Open Concept", "Balcony", "City Center"]',
                'cost' => 720000,
                'img' => 'https://placehold.co/400x300?text=Large+Apartment',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Jaspal-Large-Apartment.png',
                'bedrooms' => 3,
                'bathrooms' => 2.5,
                'stories' => 1,
                'garage_spaces' => 2,
                'kitchen_type' => 'gourmet'
            ],
            [
                'model_id' => '8Qn8QdY3B2p',
                'title' => 'Hampton Traditional',
                'description' => 'A classic Southern home showcasing detailed woodwork and a welcoming facade.',
                'style' => 'Traditional',
                'size' => 2800,
                'features' => '["Large Porch", "Hardwood Floors"]',
                'cost' => 550000,
                'img' => 'https://placehold.co/400x300?text=Hampton+Home',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Hampton-Traditional.png',
                'bedrooms' => 4,
                'bathrooms' => 3.5,
                'stories' => 2,
                'garage_spaces' => 2,
                'kitchen_type' => 'standard',
                'additional_rooms' => '["Office", "Mudroom"]'
            ],
            [
                'model_id' => 'TirmY3YcRMx',
                'title' => 'Ivy Reno Small Apartment',
                'description' => 'A stylishly renovated compact unit perfect for efficient urban living.',
                'style' => 'Apartment',
                'size' => 664,
                'features' => '["Smart Home", "Compact Living"]',
                'cost' => 250000,
                'img' => 'https://placehold.co/400x300?text=Small+Apartment',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Ivy-Reno-Small-Apartment-1.png',
                'bedrooms' => 1,
                'bathrooms' => 1.0,
                'stories' => 1,
                'garage_spaces' => 0,
                'kitchen_type' => 'basic'
            ],
            [
                'model_id' => 'godhtFY1yvz',
                'title' => 'Spacious 1-Bed Loft',
                'description' => 'A modern loft-style apartment with an open layout and an additional half bath.',
                'style' => 'Apartment',
                'size' => 1271,
                'features' => '["Open Concept", "Loft Style"]',
                'cost' => 350000,
                'img' => 'https://placehold.co/400x300?text=Spacious+Loft',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Spacious-1-Bed-Loft.png',
                'bedrooms' => 1,
                'bathrooms' => 1.5,
                'stories' => 1,
                'garage_spaces' => 1,
                'kitchen_type' => 'standard'
            ],
            [
                'model_id' => 'wcKmN4eV1NE',
                'title' => 'Alexan Studio',
                'description' => 'An efficient studio apartment located within a vibrant apartment complex.',
                'style' => 'Apartment',
                'size' => 500,
                'features' => '["Smart Home", "Efficient Layout"]',
                'cost' => 200000,
                'img' => 'https://placehold.co/400x300?text=Alexan+Studio',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Alexan-Studio.png',
                'bedrooms' => 0,
                'bathrooms' => 1.0,
                'stories' => 1,
                'garage_spaces' => 0,
                'kitchen_type' => 'basic'
            ],
            [
                'model_id' => 'YtRamwBEKMu',
                'title' => 'Okemos Family Home',
                'description' => 'A comfortable suburban family residence complete with a spacious backyard.',
                'style' => 'Traditional',
                'size' => 2000,
                'features' => '["Multiple Garages", "Fenced Yard"]',
                'cost' => 410000,
                'img' => 'https://placehold.co/400x300?text=Okemos+Home',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Okemos-Family-Home.png',
                'bedrooms' => 3,
                'bathrooms' => 2.0,
                'stories' => 1,
                'garage_spaces' => 3,
                'kitchen_type' => 'standard'
            ],
            [
                'model_id' => 'e5upBmBFH1Y',
                'title' => 'Private Luxury Estate',
                'description' => 'A secluded luxury estate boasting premium features and expansive grounds.',
                'style' => 'Luxury',
                'size' => 4000,
                'features' => '["Large Porch", "Pool", "Expansive Views"]',
                'cost' => 1100000,
                'img' => 'https://placehold.co/400x300?text=Luxury+Estate',
                'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/floor-plan-1.jpg',
                'bedrooms' => 5,
                'bathrooms' => 4.5,
                'stories' => 2,
                'garage_spaces' => 4,
                'kitchen_type' => 'luxury',
                'additional_rooms' => '["Office", "Library", "Home Theater"]',
                'flooring_type' => 'hardwood',
                'countertops_type' => 'marble',
                'cabinets_type' => 'premium',
                'appliances_type' => 'premium',
                'bath_fixtures_type' => 'premium'
            ],


[
    'model_id' => 'apT9uMacM5k',
    'title' => 'Urban Townhouse',
    'description' => 'A luxurious townhouse with modern design, a community pool, and easy access to the beach and downtown.',
    'style' => 'Contemporary',
    'size' => 1900,
    'features' => '["Private Garden", "Community Pool", "Proximity to Beach", "Modern Finishes", "Urban Setting"]',
    'cost' => 1100000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Miami+Townhouse',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Urban-Townhouse.png',
    'bedrooms' => 3,
    'bathrooms' => 2.5,
    'stories' => 2,
    'garage_spaces' => 1,
    'kitchen_type' => 'gourmet',
    'additional_rooms' => '["Veranda", "Utility Room"]',
    'flooring_type' => 'tiled'
],
[
    'model_id' => 'pemwxFawSko',
    'title' => 'Coastal Condo Retreat',
    'description' => 'A sophisticated apartment with a private balcony overlooking the Florida coastline, a communal pool, and prime beach access.',
    'style' => 'Contemporary',
    'size' => 1800,
    'features' => '["Communal Pool Access", "Private Balcony", "Ocean Views", "Proximity to Beach"]',
    'cost' => 900000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Coastal+Condo',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Coastal-Condo-Retreat.png',
    'bedrooms' => 3,
    'bathrooms' => 2,
    'stories' => 1,
    'garage_spaces' => 1,
    'kitchen_type' => 'open-plan',
    'additional_rooms' => '["Terrace"]',
    'flooring_type' => 'tiled'
],
[
    'model_id' => 'nLtY3CKSdjH',
    'title' => 'Palm Beach Luxury Penthouse',
    'description' => 'A luxurious Palm Beach penthouse with a large terrace, private jacuzzi, stunning waterfront views, and resort-style amenities.',
    'style' => 'Luxury',
    'size' => 2368,
    'features' => '["Private Jacuzzi", "Large Terrace", "Lagoon Pool Access", "Waterfront Views", "Secure Parking", "24/7 Security", "Rooftop"]',
    'cost' => 2200000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Palm+Beach+Penthouse',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Palm-Beach-Luxury-Penthouse.png',
    'bedrooms' => 3,
    'bathrooms' => 3.0,
    'stories' => 1,
    'garage_spaces' => 2,
    'kitchen_type' => 'open-plan',
    'additional_rooms' => '["TV Room", "Lounge"]',
    'flooring_type' => 'tiled'
],
[
    'model_id' => 'TSHJ6VQt9EX',
    'title' => 'Sarasota Waterfront Villa',
    'description' => 'A modern Sarasota villa with contemporary design, open spaces, high-end finishes, a private pool, and waterfront access.',
    'style' => 'Villa',
    'size' => 2150,
    'features' => '["Private Pool", "Waterfront Access", "Community Amenities", "Beach Proximity"]',
    'cost' => 950000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Sarasota+Villa',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Sarasota-Waterfront-Villa.png',
    'bedrooms' => 3,
    'bathrooms' => 3.0,
    'stories' => 2,
    'garage_spaces' => 2,
    'kitchen_type' => 'open-plan',
    'additional_rooms' => '["Patio", "Office", "Media Room"]',
    'flooring_type' => 'tiled'
],
[
    'model_id' => 'Li953TfVNwd',
    'title' => 'Orlando Family Home',
    'description' => 'A spacious single-story home in Orlando, featuring open-concept living, a modern kitchen, a generous primary suite, and a covered lanai.',
    'style' => 'Modern Ranch',
    'size' => 3006,
    'features' => '["Open-Concept Living", "Modern Kitchen", "Generous Primary Suite", "Game Room", "Private Office", "Covered Lanai"]',
    'cost' => 825000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Orlando+Family+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Orlando-Family-Home.png',
    'bedrooms' => 4,
    'bathrooms' => 3.5,
    'stories' => 1,
    'garage_spaces' => 2,
    'kitchen_type' => 'chef',
    'additional_rooms' => '["Game Room", "Private Office"]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'Gvepp1qJTDr',
    'title' => 'Naples Waterfront Estate',
    'description' => 'A spacious, multi-level Naples estate with breathtaking Gulf views, a private hot tub, a large balcony, and a gourmet kitchen.',
    'style' => 'Luxury',
    'size' => 3613,
    'features' => '["Private Hot Tub", "Outdoor Kitchen", "Balcony", "Gulf Views", "Central A/C", "Gourmet Kitchen", "Finished basement with a media room"]',
    'cost' => 3500000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Naples+Estate',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Naples-Waterfront-Estate.png',
    'bedrooms' => 4,
    'bathrooms' => 6,
    'stories' => 3,
    'garage_spaces' => 2,
    'kitchen_type' => 'Gourmet kitchen with high-end appliances',
    'additional_rooms' => '["2 Living Areas", "Media Room"]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'EgWx3WmKTpu',
    'title' => 'Gainesville Suburban Home',
    'description' => 'An expansive home in a quiet Gainesville suburb with multiple living areas and a spacious fenced-in backyard.',
    'style' => 'Traditional',
    'size' => 2440,
    'features' => '["3 bedrooms", "3 bathrooms", "Multiple Living Areas", "Fenced Backyard", "Wood Flooring", "Well-Maintained Lawn"]',
    'cost' => 550000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Gainesville+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Gainesville-Suburban-Home.png',
    'bedrooms' => 3,
    'bathrooms' => 3.0,
    'stories' => 3,
    'garage_spaces' => 0,
    'kitchen_type' => 'functional',
    'additional_rooms' => '["Basement"]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => '6yL2uKFX1NC',
    'title' => 'Orlando Vacation Home',
    'description' => 'A vacation home near Orlando\'s theme parks, perfect for large families, featuring a pool table, ping pong table, and a hot tub.',
    'style' => 'Vacation Home',
    'size' => 3500,
    'features' => '["Pool Table", "Ping Pong Table", "Hot Tub", "6 bedrooms", "Private Pool", "Game Room"]',
    'cost' => 950000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Orlando+Vacation+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Orlando-Vacation-Home.png',
    'bedrooms' => 6,
    'bathrooms' => 4.0,
    'stories' => 3,
    'garage_spaces' => 0,
    'kitchen_type' => 'full',
    'additional_rooms' => '["Game Room"]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'wWVJans1cTv',
    'title' => 'Sarasota Modern Home',
    'description' => 'A sophisticated, single-story modern home with a luxurious primary suite, 8\' doors, built-in kitchen appliances, and a private backyard with a lanai.',
    'style' => 'Modern',
    'size' => 2800,
    'features' => '["4 spacious bedrooms", "Primary suite with oversized closet", "8\' doors throughout", "Separate tub and shower in master bath", "Private backyard", "Built-in kitchen appliances"]',
    'cost' => 700000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Sarasota+Modern+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Sarasota-Modern-Home.png',
    'bedrooms' => 4,
    'bathrooms' => 3.5,
    'stories' => 1,
    'garage_spaces' => 2,
    'kitchen_type' => 'modern',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'tmWaNuUuLep',
    'title' => 'Contemporary Family Home',
    'description' => 'A beautifully crafted home on a corner lot with a game room, private office, a chef\'s kitchen, and a finished 3-car garage.',
    'style' => 'Contemporary',
    'size' => 3000,
    'features' => '["Game Room", "Private Office", "Chef\'s Kitchen", "Oversized Primary Closet", "8-Foot Doors", "Covered Patio", "Finished 3-Car Garage"]',
    'cost' => 780000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Contemporary+Family+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Contemporary-Family-Home.png',
    'bedrooms' => 4,
    'bathrooms' => 3.5,
    'stories' => 1,
    'garage_spaces' => 3,
    'kitchen_type' => 'chef',
    'additional_rooms' => '["Game Room", "Private Office"]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'frY9BJveMJj',
    'title' => 'Tampa Urban Home',
    'description' => 'A well-designed home in a vibrant Tampa neighborhood with an open-concept kitchen and living area.',
    'style' => 'Contemporary',
    'size' => 2500,
    'features' => '["Open-concept kitchen and living area", "Large kitchen island", "Modern appliances", "Separate dining room", "Laundry room", "Home office", "Attached garage", "Private backyard"]',
    'cost' => 600000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Tampa+Urban+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Tampa-Urban-Home.png',
    'bedrooms' => 3,
    'bathrooms' => 3.0,
    'stories' => 2,
    'garage_spaces' => 1,
    'kitchen_type' => 'modern',
    'additional_rooms' => '["Dining room", "Laundry room", "Home office", "Basement"]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'D5j2jGeHhZJ',
    'title' => 'North Florida Starter Home',
    'description' => 'A comfortable home in a quiet North Florida suburb with a practical living space, a privacy-fenced backyard, and a 2-car garage.',
    'style' => 'Traditional',
    'size' => 1800,
    'features' => '["3 bedrooms", "2 bathrooms", "Privacy fenced backyard", "2-car garage"]',
    'cost' => 420000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=North+Florida+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/North-Florida-Starter-Home.png',
    'bedrooms' => 3,
    'bathrooms' => 2.0,
    'stories' => 1,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'cmRpA1swnzY',
    'title' => 'Classic Florida Ranch',
    'description' => 'A classic ranch-style home with a simple layout, offering comfortable and easy Florida living.',
    'style' => 'Ranch',
    'size' => 1788,
    'features' => '["4 bedrooms", "2 baths", "2-car garage", "Single story"]',
    'cost' => 410000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Florida+Ranch+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Classic-Florida-Ranch.png',
    'bedrooms' => 4,
    'bathrooms' => 2.0,
    'stories' => 1,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => '6AEjttPT8B7',
    'title' => 'Compact Contemporary Home',
    'description' => 'An efficient and modern one-story home ideal for a starter home or for downsizing.',
    'style' => 'Contemporary',
    'size' => 1500,
    'features' => '["3 bedrooms", "2 baths", "2-car garage", "One-story"]',
    'cost' => 380000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Contemporary+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Contemporary-Home-2.png',
    'bedrooms' => 3,
    'bathrooms' => 2.0,
    'stories' => 1,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'zxdeaYJ93E3',
    'title' => 'Florida Coastal Estate',
    'description' => 'A sprawling coastal family estate with gorgeous ocean views, a private pool, and extensive outdoor living space.',
    'style' => 'Contemporary',
    'size' => 4000,
    'features' => '["Ocean views", "Private Pool", "Gourmet Kitchen", "Spacious Lanai", "Coastal Living"]',
    'cost' => 2800000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Florida+Coastal+Estate',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Florida-Coastal-Estate.png',
    'bedrooms' => 5,
    'bathrooms' => 4.5,
    'stories' => 2,
    'garage_spaces' => 3,
    'kitchen_type' => 'gourmet',
    'additional_rooms' => '["Home Office", "Game Room"]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => '63Vwm536YrA',
    'title' => 'Ocala Equestrian Estate',
    'description' => 'A gorgeous custom equestrian estate on over 5 acres in Ocala, featuring a barn with an apartment, beautiful views, and a private pickleball court.',
    'style' => 'Apartment',
    'size' => 4900,
    'features' => '["5+ acres", "Barn", "ADU", "Pickleball court", "Views of the landscape"]',
    'cost' => 1800000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Ocala+Equestrian+Estate',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Ocala-Equestrian-Estate.png',
    'bedrooms' => 5,
    'bathrooms' => 4.0,
    'stories' => 2,
    'garage_spaces' => 3,
    'kitchen_type' => 'gourmet',
    'additional_rooms' => '["Barn", "ADU", "Shop"]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => '4FeARgyARZr',
    'title' => 'Central Florida Family Home',
    'description' => 'A single-story family home offering a perfect blend of space and comfort in a suburban setting.',
    'style' => 'Traditional',
    'size' => 2024,
    'features' => '["4 bedrooms", "2 bathrooms", "Single story", "2024 sq. ft."]',
    'cost' => 480000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Florida+Family+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Central-Florida-Family-Home-1.png',
    'bedrooms' => 4,
    'bathrooms' => 2.0,
    'stories' => 1,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'bSSedu3gBdo',
    'title' => 'Suburban Two-Story Home',
    'description' => 'A spacious two-story home with a family-friendly layout in a desirable suburban Florida community.',
    'style' => 'Contemporary',
    'size' => 2473,
    'features' => '["3 bedrooms", "3 baths", "2 stories", "Spacious layout"]',
    'cost' => 590000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Suburban+Two-Story',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Suburban-Two-Story-Home.png',
    'bedrooms' => 3,
    'bathrooms' => 3.0,
    'stories' => 2,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => '1ja4YsAiUuS',
    'title' => 'Two-Story Family Home',
    'description' => 'A two-story home with a spacious game room, providing plenty of space for a growing family in a quiet, welcoming community.',
    'style' => 'Modern',
    'size' => 2462,
    'features' => '["4 bedrooms", "3 baths", "2 stories", "Spacious game room"]',
    'cost' => 580000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Two-Story+Family+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Two-Story-Family-Home.png',
    'bedrooms' => 4,
    'bathrooms' => 3.0,
    'stories' => 2,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '["Game room"]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'ajivMPGu4vF',
    'title' => 'Florida Luxury Model Home',
    'description' => 'A luxurious model home from a high-end collection, showcasing modern Florida living with top-of-the-line finishes, a gourmet kitchen, and a private lanai with a pool.',
    'style' => 'Luxury',
    'size' => 3200,
    'features' => '["High end", "Signature Collection", "Gourmet Kitchen", "Private Pool", "Lanai"]',
    'cost' => 1500000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Luxury+Model+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Florida-Luxury-Model-Home.png',
    'bedrooms' => 4,
    'bathrooms' => 4.0,
    'stories' => 1,
    'garage_spaces' => 3,
    'kitchen_type' => 'chef',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'JyM8sT3ovtc',
    'title' => 'Rural Farmhouse in North Florida',
    'description' => 'A charming country home on a large acre of land, offering open-concept living, beautiful updates, and a peaceful rural lifestyle.',
    'style' => 'Farmhouse',
    'size' => 2100,
    'features' => '["Open concept living", "Beautiful interior updates", "1 acre of land", "Shade trees", "Proximity to nearby cities"]',
    'cost' => 450000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Rural+Farmhouse',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Rural-Farmhouse-in-North-Florida.png',
    'bedrooms' => 4,
    'bathrooms' => 2.0,
    'stories' => 2,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'tLYHC536MPC',
    'title' => 'Classic Family Ranch Home',
    'description' => 'A simple and functional one-story home with a great layout, ideal for a small family.',
    'style' => 'Ranch',
    'size' => 1778,
    'features' => '["4 bedrooms", "2 baths", "One-story", "Spacious living areas"]',
    'cost' => 400000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Ranch+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Classic-Family-Ranch-Home.png',
    'bedrooms' => 4,
    'bathrooms' => 2.0,
    'stories' => 1,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'BkghJwog4PL',
    'title' => 'Tampa Modern Family Home',
    'description' => 'An exquisite Tampa residence with a chef\'s kitchen, open-concept living areas, warm wood floors, and a sleek fireplace.',
    'style' => 'Modern',
    'size' => 3100,
    'features' => '["Chef\'s kitchen", "White cabinetry", "State-of-the-art appliances", "Large island", "Open-concept living", "Warm wood floors", "Sleek fireplace", "Spacious primary bedroom"]',
    'cost' => 1200000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Tampa+Modern+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Tampa-Modern-Family-Home.png',
    'bedrooms' => 4,
    'bathrooms' => 3.5,
    'stories' => 2,
    'garage_spaces' => 2,
    'kitchen_type' => 'chef',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'RoREobXWf1d',
    'title' => 'Spacious Single-Story Home',
    'description' => 'A large single-story home with a study and an open, airy layout, perfect for a big family.',
    'style' => 'Contemporary',
    'size' => 2594,
    'features' => '["5 bedrooms", "3 bathrooms", "Study", "Single story", "2,594 square feet"]',
    'cost' => 650000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Spacious+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Spacious-Single-Story-Home.png',
    'bedrooms' => 5,
    'bathrooms' => 3.0,
    'stories' => 1,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '["Study"]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'yNd9Ef8EsND',
    'title' => 'Jacksonville Family Home',
    'description' => 'A large two-story home in a desirable Jacksonville community, with ample space for a growing family.',
    'style' => 'Traditional',
    'size' => 2800,
    'features' => '["5 bedrooms", "3 bathrooms", "2 story", "Fenced backyard"]',
    'cost' => 700000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Jacksonville+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Jacksonville-Family-Home.png',
    'bedrooms' => 5,
    'bathrooms' => 3.0,
    'stories' => 2,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => '98Q8u3Q2oTo',
    'title' => 'Affordable Florida Starter Home',
    'description' => 'A comfortable and affordable single-story home, perfect as an entry point into the housing market.',
    'style' => 'Contemporary',
    'size' => 1310,
    'features' => '["3 bedrooms", "2 bathrooms", "Single story", "1310 sq. ft."]',
    'cost' => 350000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Affordable+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Affordable-Florida-Starter-Home-2.png',
    'bedrooms' => 3,
    'bathrooms' => 2.0,
    'stories' => 1,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'aDMjvQhbdsZ',
    'title' => 'Classic Ranch Home',
    'description' => 'A one-story family home with a classic layout and a manageable size in a welcoming community.',
    'style' => 'Ranch',
    'size' => 1717,
    'features' => '["4 bedrooms", "2 baths", "One-story", "1717 square feet"]',
    'cost' => 390000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Classic+Ranch',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Classic-Ranch-Home.png',
    'bedrooms' => 4,
    'bathrooms' => 2.0,
    'stories' => 1,
    'garage_spaces' => 2,
    'kitchen_type' => 'standard',
    'additional_rooms' => '[]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'if996TgsFhk',
    'title' => 'Fort Lauderdale Family Home',
    'description' => 'A well-appointed, two-story residence in Fort Lauderdale with an office, den, and modern kitchen with stainless steel appliances.',
    'style' => 'Transitional',
    'size' => 2600,
    'features' => '["3 bedrooms", "Office", "Den", "Family room", "Two living areas", "Modern kitchen", "Walk-in shower"]',
    'cost' => 750000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Fort+Lauderdale+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Fort-Lauderdale-Family-Home.png',
    'bedrooms' => 3,
    'bathrooms' => 1.0,
    'stories' => 2,
    'garage_spaces' => 1,
    'kitchen_type' => 'modern',
    'additional_rooms' => '["Office", "Den", "Family room", "Laundry room", "Exercise area"]',
    'flooring_type' => 'mixed'
],
[
    'model_id' => 'qzRHGSahxKc',
    'title' => 'St. Augustine Historic Home',
    'description' => 'A historic St. Augustine home with great curb appeal, newer hardwood floors, arched doorways, a large kitchen, and a privacy-fenced backyard.',
    'style' => 'Contemporary',
    'size' => 2200,
    'features' => '["Newer real hardwood floors", "Arched doorways", "Large kitchen", "Finished lower level space", "Central air conditioning", "Second floor den", "Balcony", "Privacy-fenced backyard with patio"]',
    'cost' => 620000,
    'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=St+Augustine+Home',
    'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/St.-Augustine-Historic-Home.png',
    'bedrooms' => 3,
    'bathrooms' => 2.5,
    'stories' => 2,
    'garage_spaces' => 1,
    'kitchen_type' => 'standard',
    'additional_rooms' => '["Finished lower level", "Den", "Balcony"]',
    'flooring_type' => 'mixed'
]



        ];
        
        foreach ($default_data as $data) {
            $wpdb->insert($table_name, $data);
        }
    }
}
register_activation_hook(__FILE__, 'fox_create_matterport_listings_table');


function fox_insert_matterport_listings() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'matterport_model_listings6';
    
    // Check if table exists first
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        return false; // Table doesn't exist
    }

    // Default listing data to insert
    $listings = [
    [
        'model_id' => 'apT9uMacM5k',
        'title' => 'Miami Urban Townhouse',
        'description' => 'A luxurious townhouse with modern design and premium amenities',
        'style' => 'Modern',
        'size' => 1900,
        'features' => '["Private Garden", "Community Pool", "Proximity to Beach", "Modern Finishes", "Urban Setting"]',
        'cost' => 1100000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Miami+Townhouse',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 2.5,
        'stories' => 2,
        'garage_spaces' => 1,
        'kitchen_type' => 'gourmet',
        'additional_rooms' => '["Veranda", "Utility Room"]',
        'flooring_type' => 'tiled'
    ],
    [
        'model_id' => 'pemwxFawSko',
        'title' => 'Coastal Condo',
        'description' => 'A sophisticated apartment with premium amenities',
        'style' => 'Coastal',
        'size' => 1800,
        'features' => '["Communal Pool Access", "Private Balcony", "Ocean Views", "Proximity to Beach"]',
        'cost' => 900000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Coastal+Condo',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 2,
        'stories' => 1,
        'garage_spaces' => 1,
        'kitchen_type' => 'open-plan',
        'additional_rooms' => '["Terrace"]',
        'flooring_type' => 'tiled'
    ],
    [
        'model_id' => 'nLtY3CKSdjH',
        'title' => 'Luxury Penthouse',
        'description' => 'A luxurious penthouse with premium amenities',
        'style' => 'Luxury',
        'size' => 2368,
        'features' => '["Private Jacuzzi", "Large Terrace", "Lagoon Pool Access", "Waterfront Views", "Secure Parking", "24/7 Security", "Rooftop"]',
        'cost' => 2200000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Palm+Beach+Penthouse',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 3.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'open-plan',
        'additional_rooms' => '["TV Room", "Lounge"]',
        'flooring_type' => 'tiled'
    ],
    [
        'model_id' => 'TSHJ6VQt9EX',
        'title' => 'Waterfront Villa',
        'description' => 'A modern villa with contemporary design and premium finishes',
        'style' => 'Villa',
        'size' => 2150,
        'features' => '["Private Pool", "Waterfront Access", "Community Amenities", "Beach Proximity"]',
        'cost' => 950000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Sarasota+Villa',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 3.0,
        'stories' => 2,
        'garage_spaces' => 2,
        'kitchen_type' => 'open-plan',
        'additional_rooms' => '["Patio", "Office", "Media Room"]',
        'flooring_type' => 'tiled'
    ],
    [
        'model_id' => 'Li953TfVNwd',
        'title' => 'Family Home',
        'description' => 'A spacious home featuring open-concept living',
        'style' => 'Ranch',
        'size' => 3006,
        'features' => '["Open-Concept Living", "Modern Kitchen", "Generous Primary Suite", "Game Room", "Private Office", "Covered Lanai"]',
        'cost' => 825000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Orlando+Family+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 3.5,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'chef',
        'additional_rooms' => '["Game Room", "Private Office"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'Gvepp1qJTDr',
        'title' => 'Naples Waterfront Estate',
        'description' => 'A spacious, multi-level Naples estate with breathtaking Gulf views, a private hot tub, a large balcony, and a gourmet kitchen.',
        'style' => 'Luxury',
        'size' => 3613,
        'features' => '["Private Hot Tub", "Outdoor Kitchen", "Balcony", "Gulf Views", "Central A/C", "Gourmet Kitchen", "Finished basement with a media room"]',
        'cost' => 3500000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Naples+Estate',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 6,
        'stories' => 3,
        'garage_spaces' => 2,
        'kitchen_type' => 'Gourmet kitchen with high-end appliances',
        'additional_rooms' => '["2 Living Areas", "Media Room"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'EgWx3WmKTpu',
        'title' => 'Gainesville Suburban Home',
        'description' => 'An expansive home in a quiet Gainesville suburb with multiple living areas and a spacious fenced-in backyard.',
        'style' => 'Traditional',
        'size' => 2440,
        'features' => '["3 bedrooms", "3 bathrooms", "Multiple Living Areas", "Fenced Backyard", "Wood Flooring", "Well-Maintained Lawn"]',
        'cost' => 550000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Gainesville+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 3.0,
        'stories' => 3,
        'garage_spaces' => 0,
        'kitchen_type' => 'functional',
        'additional_rooms' => '["Basement"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => '6yL2uKFX1NC',
        'title' => 'Orlando Vacation Home',
        'description' => 'A vacation home near Orlando\'s theme parks, perfect for large families, featuring a pool table, ping pong table, and a hot tub.',
        'style' => 'Contemporary',
        'size' => 3500,
        'features' => '["Pool Table", "Ping Pong Table", "Hot Tub", "6 bedrooms", "Private Pool", "Game Room"]',
        'cost' => 950000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Orlando+Vacation+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 6,
        'bathrooms' => 4.0,
        'stories' => 3,
        'garage_spaces' => 0,
        'kitchen_type' => 'full',
        'additional_rooms' => '["Game Room"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'wWVJans1cTv',
        'title' => 'Sarasota Modern Home',
        'description' => 'A sophisticated, single-story modern home with a luxurious primary suite, 8\' doors, built-in kitchen appliances, and a private backyard with a lanai.',
        'style' => 'Modern',
        'size' => 2800,
        'features' => '["4 spacious bedrooms", "Primary suite with oversized closet", "8\' doors throughout", "Separate tub and shower in master bath", "Private backyard", "Built-in kitchen appliances"]',
        'cost' => 700000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Sarasota+Modern+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 3.5,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'modern',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'tmWaNuUuLep',
        'title' => 'Contemporary Family Home',
        'description' => 'A beautifully crafted home on a corner lot with a game room, private office, a chef\'s kitchen, and a finished 3-car garage.',
        'style' => 'Contemporary',
        'size' => 3000,
        'features' => '["Game Room", "Private Office", "Chef\'s Kitchen", "Oversized Primary Closet", "8-Foot Doors", "Covered Patio", "Finished 3-Car Garage"]',
        'cost' => 780000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Contemporary+Family+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 3.5,
        'stories' => 1,
        'garage_spaces' => 3,
        'kitchen_type' => 'chef',
        'additional_rooms' => '["Game Room", "Private Office"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'frY9BJveMJj',
        'title' => 'Tampa Urban Home',
        'description' => 'A well-designed home in a vibrant Tampa neighborhood with an open-concept kitchen and living area.',
        'style' => 'Contemporary',
        'size' => 2500,
        'features' => '["Open-concept kitchen and living area", "Large kitchen island", "Modern appliances", "Separate dining room", "Laundry room", "Home office", "Attached garage", "Private backyard"]',
        'cost' => 600000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Tampa+Urban+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 3.0,
        'stories' => 2,
        'garage_spaces' => 1,
        'kitchen_type' => 'modern',
        'additional_rooms' => '["Dining room", "Laundry room", "Home office", "Basement"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'D5j2jGeHhZJ',
        'title' => 'North Florida Starter Home',
        'description' => 'A comfortable home in a quiet North Florida suburb with a practical living space, a privacy-fenced backyard, and a 2-car garage.',
        'style' => 'Traditional',
        'size' => 1800,
        'features' => '["3 bedrooms", "2 bathrooms", "Privacy fenced backyard", "2-car garage"]',
        'cost' => 420000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=North+Florida+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'cmRpA1swnzY',
        'title' => 'Classic Florida Ranch',
        'description' => 'A classic ranch-style home with a simple layout, offering comfortable and easy Florida living.',
        'style' => 'Ranch',
        'size' => 1788,
        'features' => '["4 bedrooms", "2 baths", "2-car garage", "Single story"]',
        'cost' => 410000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Florida+Ranch+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => '6AEjttPT8B7',
        'title' => 'Compact Contemporary Home',
        'description' => 'An efficient and modern one-story home ideal for a starter home or for downsizing.',
        'style' => 'Contemporary',
        'size' => 1500,
        'features' => '["3 bedrooms", "2 baths", "2-car garage", "One-story"]',
        'cost' => 380000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Contemporary+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'zxdeaYJ93E3',
        'title' => 'Florida Coastal Estate',
        'description' => 'A sprawling coastal family estate with gorgeous ocean views, a private pool, and extensive outdoor living space.',
        'style' => 'Coastal',
        'size' => 4000,
        'features' => '["Ocean views", "Private Pool", "Gourmet Kitchen", "Spacious Lanai", "Coastal Living"]',
        'cost' => 2800000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Florida+Coastal+Estate',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 5,
        'bathrooms' => 4.5,
        'stories' => 2,
        'garage_spaces' => 3,
        'kitchen_type' => 'gourmet',
        'additional_rooms' => '["Home Office", "Game Room"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => '63Vwm536YrA',
        'title' => 'Ocala Equestrian Estate',
        'description' => 'A gorgeous custom equestrian estate on over 5 acres in Ocala, featuring a barn with an apartment, beautiful views, and a private pickleball court.',
        'style' => 'Farmhouse',
        'size' => 4900,
        'features' => '["5+ acres", "Barn", "ADU", "Pickleball court", "Views of the landscape"]',
        'cost' => 1800000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Ocala+Equestrian+Estate',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 5,
        'bathrooms' => 4.0,
        'stories' => 2,
        'garage_spaces' => 3,
        'kitchen_type' => 'gourmet',
        'additional_rooms' => '["Barn", "ADU", "Shop"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => '4FeARgyARZr',
        'title' => 'Central Florida Family Home',
        'description' => 'A single-story family home offering a perfect blend of space and comfort in a suburban setting.',
        'style' => 'Traditional',
        'size' => 2024,
        'features' => '["4 bedrooms", "2 bathrooms", "Single story", "2024 sq. ft."]',
        'cost' => 480000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Florida+Family+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'bSSedu3gBdo',
        'title' => 'Suburban Two-Story Home',
        'description' => 'A spacious two-story home with a family-friendly layout in a desirable suburban Florida community.',
        'style' => 'Traditional',
        'size' => 2473,
        'features' => '["3 bedrooms", "3 baths", "2 stories", "Spacious layout"]',
        'cost' => 590000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Suburban+Two-Story',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 3.0,
        'stories' => 2,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => '1ja4YsAiUuS',
        'title' => 'Two-Story Family Home',
        'description' => 'A two-story home with a spacious game room, providing plenty of space for a growing family in a quiet, welcoming community.',
        'style' => 'Traditional',
        'size' => 2462,
        'features' => '["4 bedrooms", "3 baths", "2 stories", "Spacious game room"]',
        'cost' => 580000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Two-Story+Family+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 3.0,
        'stories' => 2,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '["Game room"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'ajivMPGu4vF',
        'title' => 'Florida Luxury Model Home',
        'description' => 'A luxurious model home from a high-end collection, showcasing modern Florida living with top-of-the-line finishes.',
        'style' => 'Luxury',
        'size' => 3200,
        'features' => '["High end", "Signature Collection", "Gourmet Kitchen", "Private Pool", "Lanai"]',
        'cost' => 1500000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Luxury+Model+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 4.0,
        'stories' => 1,
        'garage_spaces' => 3,
        'kitchen_type' => 'chef',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'JyM8sT3ovtc',
        'title' => 'Rural Farmhouse in North Florida',
        'description' => 'A charming country home on a large acre of land, offering open-concept living, beautiful updates, and a peaceful rural lifestyle.',
        'style' => 'Farmhouse',
        'size' => 2100,
        'features' => '["Open concept living", "Beautiful interior updates", "1 acre of land", "Shade trees", "Proximity to nearby cities"]',
        'cost' => 450000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Rural+Farmhouse',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 2.0,
        'stories' => 2,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'tLYHC536MPC',
        'title' => 'Classic Family Ranch Home',
        'description' => 'A simple and functional one-story home with a great layout, ideal for a small family.',
        'style' => 'Ranch',
        'size' => 1778,
        'features' => '["4 bedrooms", "2 baths", "One-story", "Spacious living areas"]',
        'cost' => 400000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Ranch+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'BkghJwog4PL',
        'title' => 'Tampa Modern Family Home',
        'description' => 'An exquisite Tampa residence with a chef\'s kitchen, open-concept living areas, warm wood floors, and a sleek fireplace.',
        'style' => 'Modern',
        'size' => 3100,
        'features' => '["Chef\'s kitchen", "White cabinetry", "State-of-the-art appliances", "Large island", "Open-concept living", "Warm wood floors", "Sleek fireplace", "Spacious primary bedroom"]',
        'cost' => 1200000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Tampa+Modern+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 3.5,
        'stories' => 2,
        'garage_spaces' => 2,
        'kitchen_type' => 'chef',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'RoREobXWf1d',
        'title' => 'Spacious Single-Story Home',
        'description' => 'A large single-story home with a study and an open, airy layout, perfect for a big family.',
        'style' => 'Ranch',
        'size' => 2594,
        'features' => '["5 bedrooms", "3 bathrooms", "Study", "Single story", "2,594 square feet"]',
        'cost' => 650000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Spacious+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 5,
        'bathrooms' => 3.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '["Study"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'yNd9Ef8EsND',
        'title' => 'Jacksonville Family Home',
        'description' => 'A large two-story home in a desirable Jacksonville community, with ample space for a growing family.',
        'style' => 'Traditional',
        'size' => 2800,
        'features' => '["5 bedrooms", "3 bathrooms", "2 story", "Fenced backyard"]',
        'cost' => 700000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Jacksonville+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 5,
        'bathrooms' => 3.0,
        'stories' => 2,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => '98Q8u3Q2oTo',
        'title' => 'Affordable Florida Starter Home',
        'description' => 'A comfortable and affordable single-story home, perfect as an entry point into the housing market.',
        'style' => 'Traditional',
        'size' => 1310,
        'features' => '["3 bedrooms", "2 bathrooms", "Single story", "1310 sq. ft."]',
        'cost' => 350000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Affordable+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'aDMjvQhbdsZ',
        'title' => 'Classic Ranch Home',
        'description' => 'A one-story family home with a classic layout and a manageable size in a welcoming community.',
        'style' => 'Ranch',
        'size' => 1717,
        'features' => '["4 bedrooms", "2 baths", "One-story", "1717 square feet"]',
        'cost' => 390000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Classic+Ranch',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 4,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'standard',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'if996TgsFhk',
        'title' => 'Fort Lauderdale Family Home',
        'description' => 'A well-appointed, two-story residence in Fort Lauderdale with an office, den, and modern kitchen with stainless steel appliances.',
        'style' => 'Contemporary',
        'size' => 2600,
        'features' => '["3 bedrooms", "Office", "Den", "Family room", "Two living areas", "Modern kitchen", "Walk-in shower"]',
        'cost' => 750000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Fort+Lauderdale+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 1.0,
        'stories' => 2,
        'garage_spaces' => 1,
        'kitchen_type' => 'modern',
        'additional_rooms' => '["Office", "Den", "Family room", "Laundry room", "Exercise area"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'qzRHGSahxKc',
        'title' => 'St. Augustine Historic Home',
        'description' => 'A historic St. Augustine home with great curb appeal, newer hardwood floors, arched doorways, a large kitchen, and a privacy-fenced backyard.',
        'style' => 'Traditional',
        'size' => 2200,
        'features' => '["Newer real hardwood floors", "Arched doorways", "Large kitchen", "Finished lower level space", "Central air conditioning", "Second floor den", "Balcony", "Privacy-fenced backyard with patio"]',
        'cost' => 620000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=St+Augustine+Home',
        'floor_img_link' => 'https://placehold.co/800x600/1e3a8a/ffffff?text=Floor+Plan+Unavailable',
        'bedrooms' => 3,
        'bathrooms' => 2.5,
        'stories' => 2,
        'garage_spaces' => 1,
        'kitchen_type' => 'standard',
        'additional_rooms' => '["Finished lower level", "Den", "Balcony"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'nTyoewjc9Pg',
        'title' => 'Studio Deluxe',
        'description' => 'A contemporary studio apartment with an open layout in University Place, Washington, offering a comfortable urban living space.',
        'style' => 'Contemporary',
        'size' => 635,
        'features' => '["Open layout", "Urban setting", "Compact living"]',
        'cost' => 250000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=University+Place+Studio',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Studio-Deluxe.png',
        'bedrooms' => 0,
        'bathrooms' => 1.0,
        'stories' => 1,
        'garage_spaces' => 0,
        'kitchen_type' => 'efficiency',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'cSvpQBbtvKi',
        'title' => 'Fantastic Ranch Home',
        'description' => 'A beautifully renovated ranch home, offering a luxurious and modern living space.',
        'style' => 'Ranch',
        'size' => 1387,
        'features' => '["New construction quality", "Gut rehab", "Luxury finishes"]',
        'cost' => 325000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Ranch+Home',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Fantastic-Ranch-Home.png',
        'bedrooms' => 3,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 1,
        'kitchen_type' => 'modern',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'r65XPs7cQzi',
        'title' => 'TRU Homes - Thrill Model',
        'description' => 'Experience the perfect blend of modernity and functionality with the TRU Homes - Thrill Model, offering a spacious and affordable living space.',
        'style' => 'Modern',
        'size' => 1475,
        'features' => '["Spacious layout", "Functional design", "Affordable", "Three bedrooms", "Two bathrooms"]',
        'cost' => 285000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=TRU+Thrill+Model+Home',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/TRU-Homes-Thrill.png',
        'bedrooms' => 3,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'modern',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'jaVWSvc1shW',
        'title' => 'Traditional Home',
        'description' => 'A beautifully maintained traditional home, boasting a wood-burning fireplace, modern amenities, and a state-of-the-art network infrastructure.',
        'style' => 'Traditional',
        'size' => 2398,
        'features' => '["Wood-burning fireplace", "Sunroom", "New roof and A/C", "Cat 8 network wiring", "Custom workstations", "1.07-acre lot"]',
        'cost' => 450000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Hammond+Home',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Traditional-Home.png',
        'bedrooms' => 3,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 0,
        'kitchen_type' => 'modern',
        'additional_rooms' => '["Sunroom", "Office"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'rzdhqqyYM6c',
        'title' => 'Eaglewood Lofts - The Highland',
        'description' => 'A modern apartment with smart home features including keyless entry, fiber internet, and in-unit laundry.',
        'style' => 'Modern',
        'size' => 1046,
        'features' => '["Smart home features", "Keyless access", "Fiber internet", "In-unit washer and dryer", "2 bedrooms", "2 bathrooms"]',
        'cost' => 380000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Eaglewood+Lofts',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Eaglewood-Lofts-The-Highland.png',
        'bedrooms' => 2,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 0,
        'kitchen_type' => 'modern',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'NQFfa4EPkgY',
        'title' => 'Modular Rambler Home',
        'description' => 'A spacious, modular rambler-style home with three bedrooms, two baths, and over 2,200 square feet of living space.',
        'style' => 'Ranch',
        'size' => 2212,
        'features' => '["Modular Home", "Rambler style", "3 bedrooms", "2 baths", "2,212 square feet"]',
        'cost' => 450000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Modular+Home',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Modular-Rambler-Home.png',
        'bedrooms' => 3,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 0,
        'kitchen_type' => 'standard',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'E8unuRnDDrY',
        'title' => 'The Sedona Home Plan',
        'description' => 'A fully finished, two-story home with a double-volume great room, a flexible main-level room, 4 bedrooms, and 3.5 bathrooms, complete with a three-stall garage.',
        'style' => 'Contemporary',
        'size' => 3594,
        'features' => '["Fully finished", "Double-volume great room", "Flexible main-level room", "4 bedrooms", "3.5 bathrooms", "Three-stall garage"]',
        'cost' => 680000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Sedona+Home',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/The-Sedona-Home-Plan.png',
        'bedrooms' => 4,
        'bathrooms' => 3.5,
        'stories' => 2,
        'garage_spaces' => 3,
        'kitchen_type' => 'modern',
        'additional_rooms' => '["Flexible Room", "Laundry Room"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'rpSv2bdV7ar',
        'title' => 'Shreveport Contemporary Home',
        'description' => 'A move-in-ready new construction home with an open floor plan and premium finishes',
        'style' => 'Contemporary',
        'size' => 2000,
        'features' => '["New construction", "One-story", "Open floor plan", "Fireplace", "Kitchen island", "Pantry", "Covered patio with built-in grill", "Covered front porch", "Mudroom", "Utility room", "Window blinds throughout"]',
        'cost' => 520000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Shreveport+Home',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Shreveport-Contemporary-Home.png',
        'bedrooms' => 3,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'modern',
        'additional_rooms' => '["Mudroom", "Utility Room"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'W93jWypW6Kp',
        'title' => 'Mountain Estate Home',
        'description' => 'A stately brick home with elegant architectural details',
        'style' => 'Traditional',
        'size' => 4900,
        'features' => '["Large Acreage", "Mountain Views", "River Proximity", "Traditional Brick Construction", "Two Stories"]',
        'cost' => 1800000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Virginia+Estate',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Mountain-Estate-Home.png',
        'bedrooms' => 4,
        'bathrooms' => 4.0,
        'stories' => 2,
        'garage_spaces' => 2,
        'kitchen_type' => 'gourmet',
        'additional_rooms' => '["Den", "Office"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'nsBkHkKjwNE',
        'title' => 'Prescott Valley Contemporary',
        'description' => 'A contemporary home with sleek design and spacious living areas',
        'style' => 'Contemporary',
        'size' => 1712,
        'features' => '["Move-in ready", "Contemporary design", "Natural light", "Open-concept living", "Fireplace", "Indoor-outdoor living", "Walk-in shower", "Double sinks", "Custom closet organizer", "Wood plank tile flooring", "Plush carpeting"]',
        'cost' => 510000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Prescott+Valley+Home',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Prescott-Valley-Contemporary.png',
        'bedrooms' => 3,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'modern',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'LHUnKxmcmJx',
        'title' => 'The Sage Creek',
        'description' => 'A modern and spacious home with elegant design features',
        'style' => 'Contemporary',
        'size' => 3150,
        'features' => '["Modern design", "3,150 sq. ft. living space", "3 bedrooms", "3.5 bathrooms", "Three-car garage", "Functional layout", "Spacious and welcoming"]',
        'cost' => 850000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=The+Sage+Creek',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/The-Sage-Creek.png',
        'bedrooms' => 3,
        'bathrooms' => 3.5,
        'stories' => 2,
        'garage_spaces' => 3,
        'kitchen_type' => 'modern',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => '6zNCJRcjrik',
        'title' => 'Box Elder Contemporary Home',
        'description' => 'A home in Box Elder, SD, with a stunning kitchen featuring white cabinetry and stainless steel appliances, an open-concept design, and luxurious bathrooms.',
        'style' => 'Contemporary',
        'size' => 2000,
        'features' => '["Granite countertops", "Master suite", "Redwood deck", "Split-level design", "Open-concept living", "Sleek white cabinetry", "Stainless steel appliances", "Chic tile backsplash", "Modern bathroom fixtures", "Plush carpeting"]',
        'cost' => 434900,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Box+Elder+Contemporary+Home',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Box-Elder-Contemporary-Home.png',
        'bedrooms' => 3,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 2,
        'kitchen_type' => 'modern',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => '3y5v4EV4EW5',
        'title' => 'Contemporary Jasper Home',
        'description' => 'A thoughtfully designed home with flexible living spaces',
        'style' => 'Contemporary',
        'size' => 2501,
        'features' => '["Single-story", "2-car garage", "Modern kitchen", "Dark wood cabinetry", "Large kitchen island", "Hardwood floors", "Carpeted floors", "Laundry room", "Built-in closet systems", "Washer/dryer included", "Refrigerator included", "Backyard landscaping included", "Move-in ready"]',
        'cost' => 625000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Prescott+Valley+Home',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Contemporary-Jasper-Home.png',
        'bedrooms' => 3,
        'bathrooms' => 2.5,
        'stories' => 1,
        'garage_spaces' => 3,
        'kitchen_type' => 'modern',
        'additional_rooms' => '["Dining Room/Office", "Laundry Room"]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => '4jXBxPneaiR',
        'title' => 'Cape Coral Waterfront Condo',
        'description' => 'A waterfront condominium in Cape Coral, FL, featuring a boat dock, covered parking, and access to a community pool.',
        'style' => 'Coastal',
        'size' => 1631,
        'features' => '["Waterfront", "Boat dock", "Covered parking", "Community pool"]',
        'cost' => 550000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Cape+Coral+Condo',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Cape-Coral-Waterfront-Condo.png',
        'bedrooms' => 3,
        'bathrooms' => 2.0,
        'stories' => 1,
        'garage_spaces' => 1,
        'kitchen_type' => 'modern',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ],
    [
        'model_id' => 'XxXd1ZDPHDF',
        'title' => 'Emerald Shores Waterfront Estate',
        'description' => 'A modern masterpiece built in 2021 in the exclusive gated lake development of Emerald Shores.',
        'style' => 'Modern',
        'size' => 2912,
        'features' => '["New construction (2021)", "Gated community", "Waterfront", "Open floor plan", "10-foot ceilings", "Hardwood floors", "Granite countertops", "Chef\'s kitchen", "3-car garage", "Gas tankless water heater"]',
        'cost' => 950000,
        'img' => 'https://placehold.co/400x300/1e3a8a/ffffff?text=Jackson+Waterfront+Home',
        'floor_img_link' => 'https://foxcertifiedcontractors.com/wp-content/uploads/2025/08/Emerald-Shores-Waterfront-Estate.png',
        'bedrooms' => 5,
        'bathrooms' => 4.0,
        'stories' => 2,
        'garage_spaces' => 3,
        'kitchen_type' => 'chef',
        'additional_rooms' => '[]',
        'flooring_type' => 'mixed'
    ]
    ];

    foreach ($listings as $listing) {
        // Check if listing already exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE model_id = %s", 
            $listing['model_id']
        ));
        
        if (!$exists) {
            $wpdb->insert($table_name, $listing);
        }
    }
    
    return true;
}

// Hook to run after table creation
add_action('after_table_creation_hook', 'fox_insert_matterport_listings');

add_action('rest_api_init', function () {
    register_rest_route('fox-api/v1', '/get-featured-listings', [
        'methods' => 'GET',
        'callback' => 'get_featured_listings',
        'permission_callback' => function($request) {
            $nonce = $request->get_header('X-WP-Nonce');
            return wp_verify_nonce($nonce, 'wp_rest');
        }
    ]);
});

function get_featured_listings() {

    global $wpdb;
    $table_name = $wpdb->prefix . 'matterport_model_listings6';
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    if (empty($results)) {
        fox_create_matterport_listings_table();
        return new WP_Error('no_listings', 'No listings found', ['status' => 404]);
    }
    $active_models = [];
    $model_check=true;
    foreach ($results as $result) {
        if ($model_check) {
            // Merge Matterport details with database data
            $result['features'] = json_decode($result['features']);
            $result['id'] = $result['model_id'];
            $result['matterport_details'] = $model_check['details'] ?? [];
            $active_models[] = $result;
        }
    }

    return new WP_REST_Response([
        'status' => 'success',
        'data' => $active_models
    ], 200);
}
function check_matterport_model_active($model_id) {

    
    $embed_url = "https://my.matterport.com/show/?m=$model_id";
    
    $response = wp_remote_head($embed_url, [
        'timeout' => 5,
        'redirection' => 0
    ]);
    
    if (is_wp_error($response)) {
        error_log("Matterport check error for model $model_id: " . $response->get_error_message());
        return false;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    
    if ($response_code === 200) {
        error_log("Matterport model $model_id is active (HTTP $response_code)");
        return true;
    }
    
    error_log("Matterport model $model_id is not active (HTTP $response_code)");
    return false;
}

function check_matterport_model_active_with_api($model_id) {

    $token_id = get_option('matterport_api_token_id');
    $token_secret = get_option('matterport_api_token_secret');
    

    // Validate credentials exist
    if (empty($token_id) || empty($token_secret)) {
        error_log("Matterport API Error: Credentials (Token ID or Token Secret) are not configured in WordPress settings.");
        return ['active' => false, 'error_message' => 'API credentials missing.'];
    }

    $auth = base64_encode("$token_id:$token_secret");
    $api_url = "https://api.matterport.com/api/models/graph";
    
    $response = wp_remote_post($api_url, [
        'headers' => [
            'Authorization' => 'Basic ' . $auth,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ],
        'body' => json_encode([
            'query' => "query GetModelDetails { model(id: \"$model_id\") { id, name } }"
        ]),
        'timeout' => 20 
    ]);

    if (is_wp_error($response)) {
        $wp_error_message = $response->get_error_message();
        error_log("Matterport API Connection Failed for model " . $model_id . ": " . $wp_error_message);
        return ['active' => false, 'error_message' => 'WP Remote Post Error: ' . $wp_error_message];
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($status_code === 401) {
        error_log("Matterport API Authentication Failed for model " . $model_id . " (Status: 401 Unauthorized). Verify Token ID/Secret in Matterport Cloud and their permissions.");
        return ['active' => false, 'error_message' => 'Authentication Failed: Check Matterport API Token ID and Secret.'];
    }
    

    if (isset($body['errors'])) {
        $graphql_error_message = 'GraphQL error: ';
        foreach ($body['errors'] as $error) {
            $graphql_error_message .= ($error['message'] ?? 'Unknown GraphQL error') . '; ';
            error_log("Matterport GraphQL Error Detail: " . json_encode($error));
        }

        if (isset($body['errors'][0]['extensions']['code']) && $body['errors'][0]['extensions']['code'] === 'not.found') {
             return ['active' => false, 'error_message' => 'Model Not Found in Your Matterport Account: ' . ($body['errors'][0]['message'] ?? 'Check Model ID or Account Ownership.')];
        }
        
        return ['active' => false, 'error_message' => 'GraphQL Errors: ' . $graphql_error_message];
    }

    if ($status_code !== 200 || empty($body['data']['model'])) {
        error_log("Matterport API Error for model " . $model_id . ": Unexpected response or empty model data. Status: " . $status_code . ", Body: " . json_encode($body));
        return ['active' => false, 'error_message' => 'Unexpected API Response or Empty Model Data.'];
    }


    return [
        'active' => true,
        'details' => [
            'id' => $body['data']['model']['id'] ?? $model_id,
            'name' => $body['data']['model']['name'] ?? 'Unknown Model Name',
            'bedrooms' => 0,
            'bathrooms' => 0,
            'area_sqft' => 0,
            'floors' => 1,
            'rooms' => []
        ]
    ];
}


// delete_inactive_matterport_models
function delete_inactive_matterport_models() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'matterport_model_listings6';

    $model_ids_to_check = $wpdb->get_col("SELECT model_id FROM $table_name");

    if (empty($model_ids_to_check)) {
        error_log("No Matterport models found in the table " . $table_name . " to check.");
        return;
    }

    $inactive_model_ids = [];
    foreach ($model_ids_to_check as $model_id) {
        // check if the model is active
        if (!check_matterport_model_active($model_id)) {
            $inactive_model_ids[] = $model_id;
        }
    }

    if (!empty($inactive_model_ids)) {
        $placeholders = implode(', ', array_fill(0, count($inactive_model_ids), '%s'));
        $sql = $wpdb->prepare(
            "DELETE FROM $table_name WHERE model_id IN ($placeholders)",
            $inactive_model_ids
        );

        $deleted_rows = $wpdb->query($sql);

        if ($deleted_rows !== false) {
            error_log("Successfully deleted $deleted_rows inactive Matterport models from $table_name. IDs: " . implode(', ', $inactive_model_ids));
        } else {
            error_log("Error deleting inactive Matterport models from $table_name. WPDB Error: " . $wpdb->last_error);
        }
    } else {
        error_log("No inactive Matterport models found to delete from " . $table_name . ".");
    }
}

// Define hook for cron job
define('MATTERPORT_CLEANUP_CRON_HOOK', 'matterport_cleanup_inactive_models_cron');

// Schedule the event when plugin/theme activates optional
function schedule_matterport_cleanup_cron() {
    if (!wp_next_scheduled(MATTERPORT_CLEANUP_CRON_HOOK)) {
        wp_schedule_event(time(), 'daily', MATTERPORT_CLEANUP_CRON_HOOK); // 'daily', 'twicedaily', 'hourly'
        error_log("Matterport cleanup cron job scheduled.");
    }
}
add_action('wp', 'schedule_matterport_cleanup_cron'); 

// Link function to the scheduled hook
add_action(MATTERPORT_CLEANUP_CRON_HOOK, 'delete_inactive_matterport_models');

add_action('rest_api_init', function() {
    register_rest_route('fox-api/v1', '/test-api', [
        'methods' => 'GET',
        'callback' => function() {
            $test_model = 'JnzpTr8g1f4'; // Replace with your model ID
            $result = check_matterport_model_active($test_model);
            return new WP_REST_Response($result, 200);
        },
        'permission_callback' => '__return_true'
    ]);
});

add_action('wp_head', function() {
    echo '<script>window.foxApiSettings = {';
    echo 'root: "' . esc_url_raw(rest_url()) . '",';
    echo 'nonce: "' . wp_create_nonce('wp_rest') . '"';
    echo '};</script>';
});