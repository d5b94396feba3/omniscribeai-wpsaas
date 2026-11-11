 
  <!-- Wizard Modal -->
  <div id="style-selector-modal" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="wizard-modal-main-title">
    <div class="modal-content max-w-8xl">
    
        <!-- Modal Header with Progress Indicator -->
        <div class="mb-10 flex flex-col md:flex-row md:items-center md:justify-between">
            <h3 id="wizard-modal-main-title" class="text-3xl md:text-4xl font-extrabold text-[var(--color-dark-primary)] mb-4 md:mb-0">Find Your Dream Home</h3>
            <div class="flex flex-col md:flex-row items-center md:space-x-4">
                <span id="wizard-modal-step-text" class="text-lg font-semibold text-gray-600 mb-2 md:mb-0"></span>
            </div>
            <button class="modal-close-btn" aria-label="Close modal">
                <i class="fas fa-times text-xl" aria-hidden="true"></i>
            </button>
        </div>
 
        <!-- Progress Indicator Line -->
        <div class="w-full bg-gray-200 rounded-full h-2 mb-12 relative" id="progress-fill-wizard-container">
            <div id="progress-fill-wizard" class="absolute top-0 left-0 h-full bg-[var(--color-primary-orange)] rounded-full transition-all duration-300 ease-in-out" style="width: 0%;"></div>
            <div class="flex justify-between absolute w-full -top-2 px-1">
                <div class="progress-step-indicator flex flex-col items-center text-sm text-gray-500 font-medium active-step active-step" data-step-index="1">
                    <div class="w-4 h-4 rounded-full bg-orange-500 border-2 border-orange-500 flex items-center justify-center text-[8px] font-bold">1</div>
                    <span class="mt-2 text-center hidden sm:block">Design Style</span>
                </div>
                <div class="progress-step-indicator flex flex-col items-center text-sm text-gray-500 font-medium active-step" data-step-index="2">
                    <div class="w-4 h-4 rounded-full bg-gray-300 border-2 border-gray-300 flex items-center justify-center text-[8px] font-bold">2</div>
                    <span class="mt-2 text-center hidden sm:block">Home Size & Layout</span>
                </div>
                <div class="progress-step-indicator flex flex-col items-center text-sm text-gray-500 font-medium active-step" data-step-index="3">
                    <div class="w-4 h-4 rounded-full bg-gray-300 border-2 border-gray-300 flex items-center justify-center text-[8px] font-bold">3</div>
                    <span class="mt-2 text-center hidden sm:block">Floor Plan</span>
                </div>
                <div class="progress-step-indicator flex flex-col items-center text-sm text-gray-500 font-medium active-step" data-step-index="4">
                    <div class="w-4 h-4 rounded-full bg-gray-300 border-2 border-gray-300 flex items-center justify-center text-[8px] font-bold">4</div>
                    <span class="mt-2 text-center hidden sm:block">Interior Finishes</span>
                </div>
                <div class="progress-step-indicator flex flex-col items-center text-sm text-gray-500 font-medium active-step" data-step-index="5">
                    <div class="w-4 h-4 rounded-full bg-gray-300 border-2 border-gray-300 flex items-center justify-center text-[8px] font-bold">5</div>
                    <span class="mt-2 text-center hidden sm:block">Budget Range</span>
                </div>
                <div class="progress-step-indicator flex flex-col items-center text-sm text-gray-500 font-medium active-step" data-step-index="6">
                    <div class="w-4 h-4 rounded-full bg-gray-300 border-2 border-gray-300 flex items-center justify-center text-[8px] font-bold">6</div>
                    <span class="mt-2 text-center hidden sm:block">Premium Upgrades</span>
                </div>
                <div class="progress-step-indicator flex flex-col items-center text-sm text-gray-500 font-medium active-step" data-step-index="7">
                    <div class="w-4 h-4 rounded-full bg-gray-300 border-2 border-gray-300 flex items-center justify-center text-[8px] font-bold">7</div>
                    <span class="mt-2 text-center hidden sm:block">Must-Haves</span>
                </div>
                <div class="progress-step-indicator flex flex-col items-center text-sm text-gray-500 font-medium active-step" data-step-index="8">
                    <div class="w-4 h-4 rounded-full bg-gray-300 border-2 border-gray-300 flex items-center justify-center text-[8px] font-bold">8</div>
                    <span class="mt-2 text-center hidden sm:block">Location & Contact Info</span>
                </div>
                <div class="progress-step-indicator flex flex-col items-center text-sm text-gray-500 font-medium active-step" data-step-index="9">
                    <div class="w-4 h-4 rounded-full bg-gray-300 border-2 border-gray-300 flex items-center justify-center text-[8px] font-bold">9</div>
                    <span class="mt-2 text-center hidden sm:block">Your Custom Matches</span>
                </div>
                <div class="progress-step-indicator flex flex-col items-center text-sm text-gray-500 font-medium active-step" data-step-index="10">
                    <div class="w-4 h-4 rounded-full bg-gray-300 border-2 border-gray-300 flex items-center justify-center text-[8px] font-bold">10</div>
                    <span class="mt-2 text-center hidden sm:block">View Details</span>
                </div>
            </div>
        </div>
    
        <!-- Wizard Steps Container -->
        <div id="wizard-steps-container" class="relative">

             <!-- Loading Spinner -->
        <div id="wizard-hourglass-spinner" class="hourglass-spinner hidden">
            <div class="hourglass"></div>
            <p class="mt-4 text-[var(--color-text-medium)] text-lg font-medium">Building Custom Selections...</p>
        </div>

            <!-- Step 1: Style Preference -->
            <div id="step-1" class="wizard-step" data-step="1">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-lg mr-4">1</div>
                        <h4 class="text-xl font-semibold text-gray-800">Architectural Style Preference</h4>
                    </div>
                    <p class="text-gray-600 text-base mb-6">Choose one or more architectural styles that inspire your dream home:</p>
                    <p id="style-selection-message" class="text-red-600 text-sm mt-2 mb-4 hidden" aria-live="polite">
                        <i class="fas fa-exclamation-triangle mr-1.5" aria-hidden="true"></i> Please select at least one architectural style to proceed.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Modern -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="styles" value="Modern" class="hidden" aria-label="Modern style">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-cube text-blue-500 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Modern</span>
                                    <span class="block text-sm text-gray-500 mt-1">Clean lines, open spaces, minimalist design</span>
                                </div>
                            </div>
                        </label>
                        <!-- Traditional -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="styles" value="Traditional" class="hidden" aria-label="Traditional style">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-house-chimney text-green-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Traditional</span>
                                    <span class="block text-sm text-gray-500 mt-1">Classic details, symmetrical design, timeless appeal</span>
                                </div>
                            </div>
                        </label>
                        <!-- Farmhouse -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="styles" value="Farmhouse" class="hidden" aria-label="Farmhouse style">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-seedling text-yellow-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Farmhouse</span>
                                    <span class="block text-sm text-gray-500 mt-1">Rustic charm, wide porches, natural materials</span>
                                </div>
                            </div>
                        </label>
                        <!-- Contemporary -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="styles" value="Contemporary" class="hidden" aria-label="Contemporary style">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-lightbulb text-purple-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Contemporary</span>
                                    <span class="block text-sm text-gray-500 mt-1">Current trends, innovative materials, unique forms</span>
                                </div>
                            </div>
                        </label>
                        <!-- Luxury -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="styles" value="Luxury" class="hidden" aria-label="Luxury style">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-gem text-red-500 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Luxury</span>
                                    <span class="block text-sm text-gray-500 mt-1">High-end finishes, premium amenities, exclusive design</span>
                                </div>
                            </div>
                        </label>
                        <!-- Coastal -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="styles" value="Coastal" class="hidden" aria-label="Coastal style">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-umbrella-beach text-cyan-500 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Coastal</span>
                                    <span class="block text-sm text-gray-500 mt-1">Beach-inspired, light colors, natural textures</span>
                                </div>
                            </div>
                        </label>
                        <!-- Craftsman -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="styles" value="Craftsman" class="hidden" aria-label="Craftsman style">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-hammer text-amber-800 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Craftsman</span>
                                    <span class="block text-sm text-gray-500 mt-1">Hand-crafted details, natural wood, built-ins</span>
                                </div>
                            </div>
                        </label>
                        <!-- Mediterranean -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="styles" value="Mediterranean" class="hidden" aria-label="Mediterranean style">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-archway text-indigo-500 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Mediterranean</span>
                                    <span class="block text-sm text-gray-500 mt-1">Stucco, red-tiled roofs, arches, courtyards</span>
                                </div>
                            </div>
                        </label>
                        <!-- Ranch -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="styles" value="Ranch" class="hidden" aria-label="Ranch style">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-home text-lime-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Ranch</span>
                                    <span class="block text-sm text-gray-500 mt-1">Single-story, open layouts, low-pitched roofs</span>
                                </div>
                            </div>
                        </label>
                        <!-- Townhome -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="styles" value="Townhome" class="hidden" aria-label="Townhome style">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-city text-gray-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Townhome</span>
                                    <span class="block text-sm text-gray-500 mt-1">Multi-level, shared walls, urban/suburban living</span>
                                </div>
                            </div>
                        </label>
                        <!-- Villa -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="styles" value="Villa" class="hidden" aria-label="Villa style">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-house-flag text-rose-500 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Villa</span>
                                    <span class="block text-sm text-gray-500 mt-1">Luxurious, often with Italian/Spanish influence, private grounds</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <!-- Step 2: Size Preference -->
            <div id="step-2" class="wizard-step hidden" data-step="2">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-lg mr-4">2</div>
                        <h4 class="text-xl font-semibold text-gray-800 flex items-center">Home Size & Layout</h4>
                    </div>
                    
                    <p class="text-gray-600 text-base mb-6">Tell us your ideal size range (in square feet):</p>
                    <p id="size-selection-message" class="text-red-600 text-sm mt-2 mb-4 hidden" aria-live="polite">
                        <i class="fas fa-exclamation-triangle mr-1.5" aria-hidden="true"></i> Please complete all required fields to proceed.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="size-min" class="block text-base font-medium text-gray-700 mb-2">Minimum Square Feet <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="dropdown-container relative">
                                    <input type="number" id="size-min" name="size-min" min="500" max="10000" placeholder="e.g., 2000"
                                        class="w-full px-4 py-3 pr-10 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-primary-orange)] focus:border-transparent dropdown-input">
                                    <span class="absolute right-10 top-2.5 text-gray-400">sq ft</span>
                                    <button type="button" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 dropdown-button" aria-label="Open options">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="dropdown-options absolute hidden w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 max-h-60 overflow-auto">
                                        <div class="p-2 text-sm text-gray-500">Common sizes</div>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="500">500 sq ft</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="1000">1,000 sq ft</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="1500">1,500 sq ft</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="2000">2,000 sq ft</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="2500">2,500 sq ft</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="3000">3,000 sq ft</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="size-max" class="block text-base font-medium text-gray-700 mb-2">Maximum Square Feet <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="dropdown-container relative">
                                    <input type="number" id="size-max" name="size-max" min="500" max="10000" placeholder="e.g., 4000"
                                        class="w-full px-4 py-3 pr-10 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-primary-orange)] focus:border-transparent dropdown-input">
                                    <span class="absolute right-10 top-2.5 text-gray-400">sq ft</span>
                                    <button type="button" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 dropdown-button" aria-label="Open options">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="dropdown-options absolute hidden w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 max-h-60 overflow-auto">
                                        <div class="p-2 text-sm text-gray-500">Common sizes</div>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="1000">1,000 sq ft</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="2000">2,000 sq ft</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="3000">3,000 sq ft</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="4000">4,000 sq ft</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="5000">5,000 sq ft</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="7500">7,500 sq ft</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-[var(--color-orange-50)] border-l-4 border-[var(--color-primary-orange)] p-4 rounded-r-lg mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-[var(--color-primary-orange)] mt-1" aria-hidden="true"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-[var(--color-dark-primary)]">Most Florida homes range from 1,500-3,500 sq ft. Larger homes may require additional structural considerations and typically have higher cooling costs.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Number of Stories - Radio Button Style -->
                    <div class="mb-6">
                        <label class="block text-base font-medium text-gray-700 mb-2">Number of Stories <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 stories-options">
                            <label class="story-option">
                                <input type="radio" name="stories" value="1" class="sr-only peer" aria-labelledby="story-1-label">
                                <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-[var(--color-primary-orange)] peer-checked:bg-orange-50 hover:bg-gray-50 transition-colors">
                                    <span id="story-1-label" class="text-sm font-medium text-gray-700">1 Story</span>
                                </div>
                            </label>
                            
                            <label class="story-option">
                                <input type="radio" name="stories" value="1.5" class="sr-only peer" aria-labelledby="story-1.5-label">
                                <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-[var(--color-primary-orange)] peer-checked:bg-orange-50 hover:bg-gray-50 transition-colors">
                                    <span id="story-1.5-label" class="text-sm font-medium text-gray-700">1.5 Stories</span>
                                </div>
                            </label>
                            
                            <label class="story-option">
                                <input type="radio" name="stories" value="2" class="sr-only peer" aria-labelledby="story-2-label">
                                <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-[var(--color-primary-orange)] peer-checked:bg-orange-50 hover:bg-gray-50 transition-colors">
                                    <span id="story-2-label" class="text-sm font-medium text-gray-700">2 Stories</span>
                                </div>
                            </label>
                            
                            <label class="story-option">
                                <input type="radio" name="stories" value="3" class="sr-only peer" aria-labelledby="story-3-label">
                                <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-[var(--color-primary-orange)] peer-checked:bg-orange-50 hover:bg-gray-50 transition-colors">
                                    <span id="story-3-label" class="text-sm font-medium text-gray-700">3 Stories</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-[var(--color-orange-50)] border-l-4 border-[var(--color-primary-orange)] p-4 rounded-r-lg mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-[var(--color-primary-orange)] mt-1" aria-hidden="true"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-[var(--color-dark-primary)]">Single-story homes are most common in Florida (especially for retirees), while two-story homes offer more living space on smaller lots. 1.5-story designs often feature a bonus room above the garage.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Garage - Radio Button Style -->
                    <div class="mb-6">
                        <label class="block text-base font-medium text-gray-700 mb-2">Garage <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 garage-options">
                            <label class="garage-option">
                                <input type="radio" name="garage" value="0" class="sr-only peer" aria-labelledby="garage-0-label">
                                <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-[var(--color-primary-orange)] peer-checked:bg-orange-50 hover:bg-gray-50 transition-colors">
                                    <span id="garage-0-label" class="text-sm font-medium text-gray-700">No Garage</span>
                                </div>
                            </label>
                            
                            <label class="garage-option">
                                <input type="radio" name="garage" value="1" class="sr-only peer" aria-labelledby="garage-1-label">
                                <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-[var(--color-primary-orange)] peer-checked:bg-orange-50 hover:bg-gray-50 transition-colors">
                                    <span id="garage-1-label" class="text-sm font-medium text-gray-700">1-Car</span>
                                </div>
                            </label>
                            
                            <label class="garage-option">
                                <input type="radio" name="garage" value="2" class="sr-only peer" aria-labelledby="garage-2-label">
                                <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-[var(--color-primary-orange)] peer-checked:bg-orange-50 hover:bg-gray-50 transition-colors">
                                    <span id="garage-2-label" class="text-sm font-medium text-gray-700">2-Car</span>
                                </div>
                            </label>
                            
                            <label class="garage-option">
                                <input type="radio" name="garage" value="3" class="sr-only peer" aria-labelledby="garage-3-label">
                                <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-[var(--color-primary-orange)] peer-checked:bg-orange-50 hover:bg-gray-50 transition-colors">
                                    <span id="garage-3-label" class="text-sm font-medium text-gray-700">3-Car</span>
                                </div>
                            </label>
                            
                            <label class="garage-option">
                                <input type="radio" name="garage" value="4" class="sr-only peer" aria-labelledby="garage-4-label">
                                <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-[var(--color-primary-orange)] peer-checked:bg-orange-50 hover:bg-gray-50 transition-colors">
                                    <span id="garage-4-label" class="text-sm font-medium text-gray-700">4+ Car</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="bg-[var(--color-orange-50)] border-l-4 border-[var(--color-primary-orange)] p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-[var(--color-primary-orange)] mt-1" aria-hidden="true"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-[var(--color-dark-primary)]">2-car garages are most common in Florida. Larger garages (3+ cars) are popular for golf cart storage or as workshop space. No garage options are typically found in coastal areas or historic districts.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Step 3: Floor Plan -->
            <div id="step-3" class="wizard-step hidden" data-step="3">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-lg mr-4">3</div>
                        <h4 class="text-xl font-semibold text-gray-800 flex items-center">Floor Plan</h4>
                    </div>
                    <p class="text-gray-600 text-base mb-6">Configure your ideal room layout and quantities:</p>
                    <p id="rooms-baths-error" class="text-red-600 text-sm mt-2 mb-4 hidden" aria-live="polite">
                        <i class="fas fa-exclamation-triangle mr-1.5" aria-hidden="true"></i> Please select options for all required fields in this section.
                    </p>
                    
                    <!-- Bedrooms Selection -->
                    <div class="mb-6">
                        <label class="block text-base font-medium text-gray-700 mb-2">Number of Bedrooms <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <label class="option-card">
                                <input type="radio" name="bedrooms" value="1" class="hidden peer" aria-label="1 Bedroom">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bed text-blue-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">1 Bedroom</span>
                                            <span class="block text-sm text-gray-500 mt-1">Compact living</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="bedrooms" value="2" class="hidden peer" aria-label="2 Bedrooms">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bed text-blue-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">2 Bedrooms</span>
                                            <span class="block text-sm text-gray-500 mt-1">Small family</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="bedrooms" value="3" class="hidden peer" aria-label="3 Bedrooms">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bed text-blue-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">3 Bedrooms</span>
                                            <span class="block text-sm text-gray-500 mt-1">Average family</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="bedrooms" value="4" class="hidden peer" aria-label="4 Bedrooms">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bed text-blue-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">4 Bedrooms</span>
                                            <span class="block text-sm text-gray-500 mt-1">Growing family</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="bedrooms" value="5" class="hidden peer" aria-label="5 Bedrooms">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bed text-blue-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">5 Bedrooms</span>
                                            <span class="block text-sm text-gray-500 mt-1">Large family</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="bedrooms" value="6" class="hidden peer" aria-label="6+ Bedrooms">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bed text-blue-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">6+ Bedrooms</span>
                                            <span class="block text-sm text-gray-500 mt-1">Extra space</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Bathrooms Selection -->
                    <div class="mb-6">
                        <label class="block text-base font-medium text-gray-700 mb-2">Number of Bathrooms <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label class="option-card">
                                <input type="radio" name="bathrooms" value="1" class="hidden peer" aria-label="1 Bathroom">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bath text-teal-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">1 Bathroom</span>
                                            <span class="block text-sm text-gray-500 mt-1">Basic</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="bathrooms" value="1.5" class="hidden peer" aria-label="1.5 Bathrooms">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bath text-teal-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">1.5 Bathrooms</span>
                                            <span class="block text-sm text-gray-500 mt-1">Half bath</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="bathrooms" value="2" class="hidden peer" aria-label="2 Bathrooms">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bath text-teal-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">2 Bathrooms</span>
                                            <span class="block text-sm text-gray-500 mt-1">Standard</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="bathrooms" value="2.5" class="hidden peer" aria-label="2.5 Bathrooms">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bath text-teal-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">2.5 Bathrooms</span>
                                            <span class="block text-sm text-gray-500 mt-1">Half bath</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="bathrooms" value="3" class="hidden peer" aria-label="3 Bathrooms">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bath text-teal-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">3 Bathrooms</span>
                                            <span class="block text-sm text-gray-500 mt-1">Comfortable</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="bathrooms" value="3.5" class="hidden peer" aria-label="3.5 Bathrooms">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bath text-teal-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">3.5 Bathrooms</span>
                                            <span class="block text-sm text-gray-500 mt-1">Half bath</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="bathrooms" value="4" class="hidden peer" aria-label="4 Bathrooms">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-bath text-teal-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">4 Bathrooms</span>
                                            <span class="block text-sm text-gray-500 mt-1">Luxury</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Kitchen Type Selection -->
                    <div class="mb-6">
                        <label class="block text-base font-medium text-gray-700 mb-2">Kitchen Type <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="option-card">
                                <input type="radio" name="kitchen-type" value="basic" class="hidden peer" aria-label="Basic Kitchen">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-utensils text-amber-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Basic Kitchen</span>
                                            <span class="block text-sm text-gray-500 mt-1">Standard appliances, laminate countertops</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="kitchen-type" value="standard" class="hidden peer" aria-label="Standard Kitchen">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-utensils text-amber-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Standard Kitchen</span>
                                            <span class="block text-sm text-gray-500 mt-1">Upgraded appliances, quartz countertops</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="kitchen-type" value="gourmet" class="hidden peer" aria-label="Gourmet Kitchen">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-utensils text-amber-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Gourmet Kitchen</span>
                                            <span class="block text-sm text-gray-500 mt-1">Professional appliances, custom cabinetry</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="radio" name="kitchen-type" value="luxury" class="hidden peer" aria-label="Luxury Kitchen">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-utensils text-amber-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Luxury Kitchen</span>
                                            <span class="block text-sm text-gray-500 mt-1">High-end appliances, premium finishes</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Additional Rooms (existing checkbox implementation) -->
                    <div class="mt-6">
                        <label class="block text-base font-medium text-gray-700 mb-2">Additional Rooms (select all that apply)</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="option-card">
                                <input type="checkbox" name="additional-rooms" value="office" class="hidden peer" aria-label="Home Office">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-briefcase text-blue-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Home Office</span>
                                            <span class="block text-sm text-gray-500 mt-1">Dedicated workspace</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="checkbox" name="additional-rooms" value="mudroom" class="hidden peer" aria-label="Mudroom">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-shoe-prints text-brown-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Mudroom</span>
                                            <span class="block text-sm text-gray-500 mt-1">Entryway storage space</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="checkbox" name="additional-rooms" value="laundry" class="hidden peer" aria-label="Laundry Room">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-tshirt text-indigo-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Laundry Room</span>
                                            <span class="block text-sm text-gray-500 mt-1">Dedicated laundry space</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="option-card">
                                <input type="checkbox" name="additional-rooms" value="pantry" class="hidden peer" aria-label="Walk-in Pantry">
                                <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-utensils text-amber-500 text-xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Walk-in Pantry</span>
                                            <span class="block text-sm text-gray-500 mt-1">Extra kitchen storage</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Step 4: Finishes -->
            <div id="step-4" class="wizard-step hidden" data-step="4">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-lg mr-4">4</div>
                        <h4 class="text-xl font-semibold text-gray-800 flex items-center">Interior Finishes</h4>
                    </div>
                    <p class="text-gray-600 text-base mb-6">Select your preferred materials and finishes for floors and surfaces:</p>
                    <p id="finishes-error" class="text-red-600 text-sm mt-2 mb-4 hidden" aria-live="polite">
                        <i class="fas fa-exclamation-triangle mr-1.5" aria-hidden="true"></i> Please select options for all required finishes.
                    </p>
                    
                    <div class="space-y-8">
                        <div class="form-group" data-option-type="flooring">
                            <label class="block text-base font-medium text-gray-700 mb-2">Flooring <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="flooring" data-value="laminate">
                                    <input type="radio" name="flooring" value="laminate" class="hidden" aria-label="Laminate Flooring">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-square text-amber-600 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Laminate</span>
                                            <span class="block text-sm text-gray-500 mt-1">$2 - $5 per sq.ft.</span>
                                            <span class="block text-xs text-gray-400 mt-1">Durable and affordable</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="flooring" data-value="engineered">
                                    <input type="radio" name="flooring" value="engineered" class="hidden" aria-label="Engineered Hardwood">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-tree text-brown-600 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Engineered Hardwood</span>
                                            <span class="block text-sm text-gray-500 mt-1">$5 - $10 per sq.ft.</span>
                                            <span class="block text-xs text-gray-400 mt-1">High-quality engineered wood</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="flooring" data-value="hardwood">
                                    <input type="radio" name="flooring" value="hardwood" class="hidden" aria-label="Solid Hardwood">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-tree text-brown-800 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Solid Hardwood</span>
                                            <span class="block text-sm text-gray-500 mt-1">$8 - $15 per sq.ft.</span>
                                            <span class="block text-xs text-gray-400 mt-1">Premium solid hardwood</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="flooring" data-value="tile">
                                    <input type="radio" name="flooring" value="tile" class="hidden" aria-label="Porcelain Tile">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-square text-gray-500 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Porcelain Tile</span>
                                            <span class="block text-sm text-gray-500 mt-1">$4 - $12 per sq.ft.</span>
                                            <span class="block text-xs text-gray-400 mt-1">Durable and stylish</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group" data-option-type="countertops">
                            <label class="block text-base font-medium text-gray-700 mb-2">Countertops <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="countertops" data-value="laminate">
                                    <input type="radio" name="countertops" value="laminate" class="hidden" aria-label="Laminate Countertops">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-square text-amber-400 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Laminate</span>
                                            <span class="block text-sm text-gray-500 mt-1">$20 - $50 per sq.ft.</span>
                                            <span class="block text-xs text-gray-400 mt-1">Budget-friendly</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="countertops" data-value="quartz">
                                    <input type="radio" name="countertops" value="quartz" class="hidden" aria-label="Quartz Countertops">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-gem text-gray-400 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Quartz</span>
                                            <span class="block text-sm text-gray-500 mt-1">$50 - $120 per sq.ft.</span>
                                            <span class="block text-xs text-gray-400 mt-1">Durable and low-maintenance</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="countertops" data-value="granite">
                                    <input type="radio" name="countertops" value="granite" class="hidden" aria-label="Granite Countertops">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-mountain text-gray-600 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Granite</span>
                                            <span class="block text-sm text-gray-500 mt-1">$40 - $100 per sq.ft.</span>
                                            <span class="block text-xs text-gray-400 mt-1">Natural stone with unique patterns</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="countertops" data-value="marble">
                                    <input type="radio" name="countertops" value="marble" class="hidden" aria-label="Marble Countertops">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-mountain text-gray-300 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Marble</span>
                                            <span class="block text-sm text-gray-500 mt-1">$75 - $200 per sq.ft.</span>
                                            <span class="block text-xs text-gray-400 mt-1">Luxurious natural marble</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Step 5: Budget -->
            <div id="step-5" class="wizard-step hidden" data-step="5">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-lg mr-4">5</div>
                        <h4 class="text-xl font-semibold text-gray-800 flex items-center">Budget Range</h4>
                    </div>
                    <p class="text-gray-600 text-base mb-6">Set your comfortable investment range for the complete project:</p>
                    <p id="budget-selection-message" class="text-red-600 text-sm mt-2 mb-4 hidden" aria-live="polite">
                        <i class="fas fa-exclamation-triangle mr-1.5" aria-hidden="true"></i> Please enter both minimum and maximum budget to proceed.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="budget-min" class="block text-base font-medium text-gray-700 mb-2">Minimum Budget <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <!-- Custom dropdown container -->
                                <div class="dropdown-container relative">
                                    <span class="absolute left-4 top-3.5 text-gray-400">$</span>
                                    <input type="number" id="budget-min" name="budget-min" min="200000" max="5000000" placeholder="e.g., 500000"
                                        class="w-full px-4 py-3 pl-10 pr-10 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-primary-orange)] focus:border-transparent dropdown-input">
                                    <!-- Dropdown trigger button -->
                                    <button type="button" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 dropdown-button" aria-label="Open options">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <!-- Dropdown options -->
                                    <div class="dropdown-options absolute hidden w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 max-h-60 overflow-auto">
                                        <div class="p-2 text-sm text-gray-500">Common minimum budgets</div>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="200000">$200,000</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="300000">$300,000</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="400000">$400,000</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="500000">$500,000</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="750000">$750,000</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="1000000">$1,000,000</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="budget-max" class="block text-base font-medium text-gray-700 mb-2">Maximum Budget <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <!-- Custom dropdown container -->
                                <div class="dropdown-container relative">
                                    <span class="absolute left-4 top-3.5 text-gray-400">$</span>
                                    <input type="number" id="budget-max" name="budget-max" min="200000" max="50000000" placeholder="e.g., 1000000"
                                        class="w-full px-4 py-3 pl-10 pr-10 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-primary-orange)] focus:border-transparent dropdown-input">
                                    <!-- Dropdown trigger button -->
                                    <button type="button" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 dropdown-button" aria-label="Open options">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <!-- Dropdown options -->
                                    <div class="dropdown-options absolute hidden w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 max-h-60 overflow-auto">
                                        <div class="p-2 text-sm text-gray-500">Common maximum budgets <span class="text-red-500">*</span></div>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="500000">$500,000</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="750000">$750,000</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="1000000">$1,000,000</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="1500000">$1,500,000</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="2000000">$2,000,000</button>
                                        <button type="button" class="dropdown-option w-full text-left px-4 py-2 hover:bg-gray-100" data-value="3000000">$3,000,000</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-[var(--color-orange-50)] border-l-4 border-[var(--color-primary-orange)] p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-[var(--color-primary-orange)] mt-1" aria-hidden="true"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-[var(--color-dark-primary)]">Consider your total project cost, including land, construction, and finishes.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
 
            <!-- Step 6: Allowances -->
            <div id="step-6" class="wizard-step hidden" data-step="6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-lg mr-4">6</div>
                        <h4 class="text-xl font-semibold text-gray-800 flex items-center">Premium Upgrades</h4>
                    </div>
                    <p class="text-gray-600 text-base mb-6">Select which high-end features matter most in your home:</p>
                    <p id="allowances-error" class="text-red-600 text-sm mt-2 mb-4 hidden" aria-live="polite">
                        <i class="fas fa-exclamation-triangle mr-1.5" aria-hidden="true"></i> Please select options for all required allowances.
                    </p>
                    
                    <div class="space-y-8">
                        <div class="form-group" data-option-type="cabinets">
                            <label class="block text-base font-medium text-gray-700 mb-2">Kitchen Cabinets <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="cabinets" data-value="basic">
                                    <input type="radio" name="cabinets" value="basic" class="hidden" aria-label="Basic Cabinets">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-kitchen-set text-orange-500 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Basic Cabinets</span>
                                            <span class="block text-sm text-gray-500 mt-1">$15,000 - $25,000</span>
                                            <span class="block text-xs text-gray-400 mt-1">Stock cabinets with limited options</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="cabinets" data-value="standard">
                                    <input type="radio" name="cabinets" value="standard" class="hidden" aria-label="Standard Cabinets">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-kitchen-set text-orange-500 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Standard Cabinets</span>
                                            <span class="block text-sm text-gray-500 mt-1">$25,000 - $35,000</span>
                                            <span class="block text-xs text-gray-400 mt-1">Semi-custom with more options</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="cabinets" data-value="premium">
                                    <input type="radio" name="cabinets" value="premium" class="hidden" aria-label="Premium Cabinets">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-kitchen-set text-orange-500 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Premium Cabinets</span>
                                            <span class="block text-sm text-gray-500 mt-1">$35,000 - $50,000+</span>
                                            <span class="block text-xs text-gray-400 mt-1">Custom cabinets with high-end finishes</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group" data-option-type="appliances">
                            <label class="block text-base font-medium text-gray-700 mb-2">Appliances <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="appliances" data-value="basic">
                                    <input type="radio" name="appliances" value="basic" class="hidden" aria-label="Basic Appliances">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-blender text-gray-500 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Basic Package</span>
                                            <span class="block text-sm text-gray-500 mt-1">$5,000 - $8,000</span>
                                            <span class="block text-xs text-gray-400 mt-1">Standard appliances</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="appliances" data-value="standard">
                                    <input type="radio" name="appliances" value="standard" class="hidden" aria-label="Standard Appliances">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-blender text-gray-600 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Standard Package</span>
                                            <span class="block text-sm text-gray-500 mt-1">$8,000 - $12,000</span>
                                            <span class="block text-xs text-gray-400 mt-1">Upgraded appliances</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="appliances" data-value="premium">
                                    <input type="radio" name="appliances" value="premium" class="hidden" aria-label="Premium Appliances">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-blender text-gray-700 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Premium Package</span>
                                            <span class="block text-sm text-gray-500 mt-1">$12,000 - $25,000+</span>
                                            <span class="block text-xs text-gray-400 mt-1">High-end professional-grade</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group" data-option-type="bath-fixtures">
                            <label class="block text-base font-medium text-gray-700 mb-2">Bathroom Fixtures <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="bath-fixtures" data-value="basic">
                                    <input type="radio" name="bath-fixtures" value="basic" class="hidden" aria-label="Basic Bath Fixtures">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-bath text-blue-400 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Basic Fixtures</span>
                                            <span class="block text-sm text-gray-500 mt-1">$2,000 - $4,000 per bathroom</span>
                                            <span class="block text-xs text-gray-400 mt-1">Standard quality</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="bath-fixtures" data-value="standard">
                                    <input type="radio" name="bath-fixtures" value="standard" class="hidden" aria-label="Standard Bath Fixtures">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-bath text-blue-500 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Standard Fixtures</span>
                                            <span class="block text-sm text-gray-500 mt-1">$4,000 - $7,000 per bathroom</span>
                                            <span class="block text-xs text-gray-400 mt-1">Upgraded fixtures</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card" data-option="bath-fixtures" data-value="premium">
                                    <input type="radio" name="bath-fixtures" value="premium" class="hidden" aria-label="Premium Bath Fixtures">
                                    <div class="ml-4 flex items-center">
                                        <i class="fas fa-bath text-blue-600 text-2xl mr-3" aria-hidden="true"></i>
                                        <div>
                                            <span class="block font-medium text-gray-800">Premium Fixtures</span>
                                            <span class="block text-sm text-gray-500 mt-1">$7,000 - $15,000+ per bathroom</span>
                                            <span class="block text-xs text-gray-400 mt-1">Luxury designer fixtures</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
 
            <!-- Step 7: Key Features -->
            <div id="step-7" class="wizard-step hidden" data-step="7">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-lg mr-4">7</div>
                        <h4 class="text-xl font-semibold text-gray-800">Must-Have Features</h4>
                    </div>
                    <p class="text-gray-600 text-base mb-6">Choose your essential features that your home must include:</p>
                    <p id="features-selection-message" class="text-red-600 text-sm mt-2 mb-4 hidden" aria-live="polite">
                        <i class="fas fa-exclamation-triangle mr-1.5" aria-hidden="true"></i> Please select at least one feature to proceed.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Open Concept" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Open Concept layout">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-expand text-gray-500 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Open Concept</span>
                                    <span class="block text-sm text-gray-500 mt-1">Flowing spaces between living areas</span>
                                </div>
                            </div>
                        </label>
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Smart Home" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Smart Home Technology">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-house-signal text-purple-500 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Smart Home</span>
                                    <span class="block text-sm text-gray-500 mt-1">Automated systems and technology</span>
                                </div>
                            </div>
                        </label>
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Large Porch" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Large Porch">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-chair text-green-500 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Large Porch/Lanai</span>
                                    <span class="block text-sm text-gray-500 mt-1">Expansive outdoor living space</span>
                                </div>
                            </div>
                        </label>
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Multiple Garages" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Multiple Garages">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-warehouse text-red-500 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Multiple Garages</span>
                                    <span class="block text-sm text-gray-500 mt-1">Extra vehicle or storage space</span>
                                </div>
                            </div>
                        </label>
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Balcony" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Balcony">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-hotel text-[var(--color-primary-orange)] text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Balcony</span>
                                    <span class="block text-sm text-gray-500 mt-1">Outdoor area attached to upper floor</span>
                                </div>
                            </div>
                        </label>
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Fireplace" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Fireplace">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-fire text-red-700 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Fireplace</span>
                                    <span class="block text-sm text-gray-500 mt-1">Indoor heating and ambiance feature</span>
                                </div>
                            </div>
                        </label>
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Pool" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Pool">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-swimming-pool text-cyan-500 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Pool</span>
                                    <span class="block text-sm text-gray-500 mt-1">Private swimming pool</span>
                                </div>
                            </div>
                        </label>
                        <!-- Office -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Office" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Dedicated Office space">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-briefcase text-blue-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Office</span>
                                    <span class="block text-sm text-gray-500 mt-1">Dedicated workspace for productivity</span>
                                </div>
                            </div>
                        </label>
                        <!-- Waterfront -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Waterfront" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Waterfront property">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-water text-cyan-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Waterfront</span>
                                    <span class="block text-sm text-gray-500 mt-1">Direct access or views to a body of water</span>
                                </div>
                            </div>
                        </label>
                        <!-- Golf Course View -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Golf Course View" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Golf Course View">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-golf-ball text-green-700 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Golf Course View</span>
                                    <span class="block text-sm text-gray-500 mt-1">Scenic views overlooking a golf course</span>
                                </div>
                            </div>
                        </label>
                        <!-- Wine Cellar -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Wine Cellar" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Wine Cellar">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-wine-bottle text-purple-700 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Wine Cellar</span>
                                    <span class="block text-sm text-gray-500 mt-1">Temperature-controlled storage for wine collection</span>
                                </div>
                            </div>
                        </label>
                        <!-- Home Theater -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Home Theater" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Home Theater">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-film text-indigo-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Home Theater</span>
                                    <span class="block text-sm text-gray-500 mt-1">Dedicated room for cinematic entertainment</span>
                                </div>
                            </div>
                        </label>
                        <!-- Gym -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Gym" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Home Gym">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-dumbbell text-red-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Gym</span>
                                    <span class="block text-sm text-gray-500 mt-1">Private space for fitness and workouts</span>
                                </div>
                            </div>
                        </label>
                        <!-- Library -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Library" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Home Library">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-book-open text-yellow-700 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Library</span>
                                    <span class="block text-sm text-gray-500 mt-1">Quiet room for reading and study</span>
                                </div>
                            </div>
                        </label>
                        <!-- Guest House -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Guest House" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Guest House">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-house-user text-teal-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Guest House</span>
                                    <span class="block text-sm text-gray-500 mt-1">Separate dwelling for guests or extended family</span>
                                </div>
                            </div>
                        </label>
                        <!-- Elevator -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Elevator" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Elevator">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-elevator text-gray-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Elevator</span>
                                    <span class="block text-sm text-gray-500 mt-1">Convenient access between multiple floors</span>
                                </div>
                            </div>
                        </label>
                        <!-- Walk-in Closet -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Walk-in Closet" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Walk-in Closet">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-person-booth text-pink-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Walk-in Closet</span>
                                    <span class="block text-sm text-gray-500 mt-1">Spacious closet for extensive wardrobe</span>
                                </div>
                            </div>
                        </label>
                        <!-- Solar Panels -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Solar Panels" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Solar Panels">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-solar-panel text-orange-600 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Solar Panels</span>
                                    <span class="block text-sm text-gray-500 mt-1">Energy-efficient power generation</span>
                                </div>
                            </div>
                        </label>
                        <!-- Hurricane Windows -->
                        <label class="flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 option-card">
                            <input type="checkbox" name="features" value="Hurricane Windows" class="mt-1 form-checkbox h-5 w-5 text-orange-500 hidden" aria-label="Hurricane Windows">
                            <div class="ml-4 flex items-center">
                                <i class="fas fa-bolt text-blue-800 text-2xl mr-3" aria-hidden="true"></i>
                                <div>
                                    <span class="block font-medium text-gray-800">Hurricane Windows</span>
                                    <span class="block text-sm text-gray-500 mt-1">Impact-resistant windows for storm protection</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
 
            <!-- Step 8: Property Location & Contact Information -->
            <div id="step-8" class="wizard-step hidden" data-step="8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-lg mr-4">8</div>
                        <h4 class="text-xl font-semibold text-gray-800">Location & Contact Info</h4>
                    </div>
                    <p class="text-gray-600 text-base mb-6">Tell us about your property location and how we can reach you:</p>
                    
                    <p id="contact-error-message" class="text-red-600 text-sm mt-2 mb-4 hidden" aria-live="polite">
                        <i class="fas fa-exclamation-triangle mr-1.5" aria-hidden="true"></i> Please fill in all required contact information fields correctly.
                    </p>

                    <div class="space-y-6">
                        <!-- Property Location Section -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Property Location Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label for="property-street" class="block text-base font-medium text-gray-700 mb-2">
                                        <i class="fas fa-map-marker-alt text-gray-400 mr-2" aria-hidden="true"></i>Street Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="property-street" name="property-street" placeholder="e.g., 123 Main St"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-primary-orange)] focus:border-transparent">
                                </div>

                                <div>
                                    <label for="property-city" class="block text-base font-medium text-gray-700 mb-2">
                                        <i class="fas fa-city text-gray-400 mr-2" aria-hidden="true"></i>City <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="property-city" name="property-city" placeholder="e.g., Orlando"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-primary-orange)] focus:border-transparent">
                                </div>

                                <div>
                                    <label for="property-zip" class="block text-base font-medium text-gray-700 mb-2">
                                        <i class="fas fa-mail-bulk text-gray-400 mr-2" aria-hidden="true"></i>ZIP Code <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="property-zip" name="property-zip" placeholder="e.g., 32801" maxlength="5"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-primary-orange)] focus:border-transparent">
                                    <span id="wizard-zip-error-msg" class="mt-2 text-xs text-red-600 hidden"></span>
                                    <p class="mt-2 text-xs text-gray-500 flex items-start">
                                        <i class="fas fa-info-circle text-gray-400 mr-1.5 mt-0.5"></i>
                                        We currently serve Florida locations (ZIP codes starting with 32, 33, or 34)
                                    </p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="property-state" class="block text-base font-medium text-gray-700 mb-2">
                                    <i class="fas fa-flag-usa text-gray-400 mr-2" aria-hidden="true"></i>State
                                </label>
                                <input type="text" id="property-state" name="property-state" value="Florida" readonly
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                            </div>

                            <div class="mt-6">
                                <label for="survey-upload" class="block text-base font-medium text-gray-700 mb-2">
                                    <i class="fas fa-file-upload text-gray-400 mr-2" aria-hidden="true"></i>Upload Property Survey (Optional)
                                </label>
                                <div class="mt-1 flex items-center">
                                    <label for="survey-upload" class="cursor-pointer">
                                        <span class="inline-flex items-center px-4 py-3 border-2 border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-primary-orange)]">
                                            <i class="fas fa-upload mr-2 text-orange-500"></i>
                                            Choose File
                                        </span>
                                        <input id="survey-upload" name="survey-upload" type="file" class="sr-only" accept=".pdf,.dwg,.dxf,.jpg,.jpeg,.png">
                                    </label>
                                    <span id="file-name" class="ml-4 text-sm text-gray-500">No file selected</span>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 flex items-start">
                                    <i class="fas fa-info-circle text-gray-400 mr-1.5 mt-0.5"></i>
                                    Uploading a survey helps us ensure the home fits your lot perfectly.
                                </p>
                            </div>

                            <div class="mt-6 flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is-renovation" name="is-renovation" type="checkbox" class="focus:ring-[var(--color-primary-orange)] h-5 w-5 text-orange-500 border-2 border-gray-200 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is-renovation" class="font-medium text-gray-700">
                                        This is a renovation project
                                    </label>
                                    <p class="text-gray-500">Check this box if you're modifying an existing structure rather than new construction.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Your Contact Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label for="contact-name" class="block text-base font-medium text-gray-700 mb-2">
                                        <i class="fas fa-user text-gray-400 mr-2" aria-hidden="true"></i>Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="contact-name" name="contact-name" placeholder="Your full name" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-primary-orange)] focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label for="contact-email" class="block text-base font-medium text-gray-700 mb-2">
                                        <i class="fas fa-envelope text-gray-400 mr-2" aria-hidden="true"></i>Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" id="contact-email" name="contact-email" placeholder="your.email@example.com" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-primary-orange)] focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label for="contact-phone" class="block text-base font-medium text-gray-700 mb-2">
                                        <i class="fas fa-phone text-gray-400 mr-2" aria-hidden="true"></i>Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" id="contact-phone" name="contact-phone" placeholder="(123) 456-7890" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-primary-orange)] focus:border-transparent">
                                    <span id="wizard-phone-error-msg" class="mt-2 text-xs text-red-600 hidden"></span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="contact-message" class="block text-base font-medium text-gray-700 mb-2">
                                    <i class="fas fa-comment-alt text-gray-400 mr-2" aria-hidden="true"></i>Additional Details (Optional)
                                </label>
                                <textarea id="contact-message" name="contact-message" rows="3" placeholder="Any additional details about your project..."
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-primary-orange)] focus:border-transparent"></textarea>
                            </div>

                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="email-list-signup" name="email-list-signup" type="checkbox" class="focus:ring-[var(--color-primary-orange)] h-5 w-5 text-orange-500 border-2 border-gray-200 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="email-list-signup" class="font-medium text-gray-700">
                                        Sign up for updates and promotions
                                    </label>
                                    <p class="text-gray-500">Receive updates about new home designs, promotions, and helpful tips.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
 
            <!-- Step 9: Results Gallery -->
            <div id="step-9" class="wizard-step hidden" data-step="9">
                <h4 class="text-xl font-semibold text-gray-800 mb-4 text-center">Your Perfect Matches</h4>
                <p class="text-gray-700 mb-6 text-center" id="results-intro-text">We've found these home designs that match your preferences:</p>
                <div id="matterport-gallery-wizard" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Matterport tour cards will be dynamically inserted here -->
                </div>
                <div id="no-results-wizard" class="empty-state py-12 text-center hidden">
                    <p class="mt-4 p-4 bg-[var(--color-orange-50)] border border-[var(--color-orange-200)] text-[var(--color-orange-800)] rounded-lg text-sm flex items-start space-x-1" id="no-results-message">
                        <i class="fas fa-info-circle text-[var(--color-primary-orange)] h-5 w-5 flex-shrink-0 mt-0"></i>
                        <span>
                          <span class="font-semibold">No exact match found.</span> Here are our recommended styles for your dream house. <a href="/contact-us" target="_blank" rel="noopener noreferrer" class="text-[var(--color-primary-orange)] hover:underline font-medium">Contact us for a custom consultation</a>.
                        </span>
                      </p>
                      
                      <div id="recommended-listings-container" class="mt-8">
                        <div id="recommended-matterport-gallery" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Recommended models will be inserted here -->
                        </div>
                       
                    </div>
                </div>
            </div>
 
            <!-- Step 10: Matterport Viewer and Details -->
            <div id="step-10" class="wizard-step hidden" data-step="10">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <p class="text-gray-500 mt-0" id="wizard-selected-model-title">
                            <span class="inline-block bg-[var(--color-primary-orange)] text-white text-sm font-bold px-3 py-1 rounded-full uppercase mr-2">
                                <!-- Model style will be inserted here by JavaScript -->
                            </span>
                            Explore this home in immersive 3D
                        </p>
                    </div>
                   
                </div>
 
                <!-- Matterport Viewer -->
                <div class="relative rounded-xl overflow-hidden shadow-lg mb-8 border border-gray-200 bg-gray-50">
                    <div class="aspect-w-16 aspect-h-9">
                        <iframe id="matterport-viewer-wizard" 
                                width="100%" 
                                height="500" 
                                frameborder="0" 
                                allowfullscreen
                                allow="xr-spatial-tracking" 
                                class="w-full h-[500px]"
                                aria-label="3D Matterport tour of selected home">
                        </iframe>
                    </div>
                    
                </div>
 
                <!-- NEW: Tab Navigation for Details -->
                <div id="details-menu" class="mb-6" role="tablist">
                    <button id="tab-property-details" class="tab-btn active" data-target="section-property-details" aria-selected="true" role="tab" tabindex="0">
                        <i class="fas fa-info-circle mr-2" aria-hidden="true"></i> Property Details
                    </button>
                    <button id="tab-cost-Estimation" class="tab-btn" data-target="section-cost-Estimation" aria-selected="false" role="tab" tabindex="-1">
                        <i class="fas fa-calculator mr-2" aria-hidden="true"></i> Cost Estimation
                    </button>
                    <button id="tab-floor-plan" class="tab-btn" data-target="section-floor-plan" aria-selected="false" role="tab" tabindex="-1">
                        <i class="fas fa-vector-square mr-2" aria-hidden="true"></i> Floor Plan
                    </button>
                    <button id="tab-compliance" class="tab-btn" data-target="section-compliance" aria-selected="false" role="tab" tabindex="-1">
                        <i class="fas fa-shield-alt mr-2" aria-hidden="true"></i> Compliance
                    </button>
                </div>
                
                <!-- Property Details Section -->
                <div id="section-property-details" class="details-section">
                    <div class="property-details-card">
                        <div class="property-details-header">
                            <h3>
                                <i class="fas fa-home" aria-hidden="true"></i>
                                Property Overview
                            </h3>
                        </div>
                        
                        <div class="property-details-content">
                            <!-- dynamically generated content-->
                            <div class="detail-section">
                                <!-- dynamically generated content-->
                                <div id="user-selections-summary" class="selections-grid">
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                
                <div id="section-cost-Estimation" class="details-section hidden mt-6">
                    <!-- Cost Estimate Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 border-b border-orange-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-calculator text-orange-500 mr-3" aria-hidden="true"></i>
                                Cost Estimation
                            </h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="flex justify-between items-center bg-orange-50 rounded-lg p-4 border border-orange-100">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Estimated Total Cost</p>
                                    <p class="text-xs text-gray-500">For construction & finishes</p>
                                </div>
                                <span class="text-2xl font-bold text-[var(--color-primary-orange)]" id="wizard-estimated-cost">$---</span>
                            </div>
                            
                            <div id="wizard-cost-details" class="space-y-4">
                                <!-- Cost Estimation will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>

                <div id="section-floor-plan" class="details-section hidden mt-6">
                    <!-- Floor Plan Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 border-b border-orange-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-vector-square text-[var(--color-primary-orange)] mr-3" aria-hidden="true"></i>
                                Floor Plan
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row gap-6">
                                <div class="flex-1">
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <img id="floor-plan-image" src="" alt="Floor plan" class="w-full h-auto object-contain max-h-[400px]">
                                    </div>
                                </div>
                                <div class="md:w-1/3">
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <h4 class="font-medium text-gray-800 mb-3">Download Floor Plan</h4>
                                        <p class="text-sm text-gray-600 mb-4">Get a high-resolution version of this floor plan for your records or to share with your architect.</p>
                                        <button id="download-floor-plan-btn" class="btn-primary w-full flex items-center justify-center px-4 py-2 rounded-lg text-white font-medium hover:bg-orange-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                                            <i class="fas fa-download mr-2" aria-hidden="true"></i> Download Floor Plan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="section-compliance" class="details-section hidden mt-6">
                    <!-- Compliance Section (Full Width) -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
                        <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 border-b border-orange-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-shield-alt text-[var(--color-primary-orange)] mr-3" aria-hidden="true"></i>
                                Florida Building Compliance
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="wizard-compliance-details">
                                <div class="flex items-start p-4 bg-orange-50 rounded-lg border border-orange-100">
                                    <div class="flex-shrink-0 p-2 bg-orange-100 rounded-full mr-4">
                                        <i class="fas fa-wind text-orange-500" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">Wind Protection</h4>
                                        <p class="text-sm text-gray-600 mt-1">Rated for 140+ mph winds</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start p-4 bg-orange-50 rounded-lg border border-orange-100">
                                    <div class="flex-shrink-0 p-2 bg-orange-100 rounded-full mr-4">
                                        <i class="fas fa-water text-orange-500" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">Flood Resistance</h4>
                                        <p class="text-sm text-gray-600 mt-1">Elevation compliant</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start p-4 bg-orange-50 rounded-lg border border-orange-100">
                                    <div class="flex-shrink-0 p-2 bg-orange-100 rounded-full mr-4">
                                        <i class="fas fa-fire text-orange-500" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">Fire Safety</h4>
                                        <p class="text-sm text-gray-600 mt-1">Code-compliant materials</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start p-4 bg-orange-50 rounded-lg border border-orange-100">
                                    <div class="flex-shrink-0 p-2 bg-orange-100 rounded-full mr-4">
                                        <i class="fas fa-bolt text-orange-500" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">Energy Efficient</h4>
                                        <p class="text-sm text-gray-600 mt-1">Meets Florida standards</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Next Steps</h3>
                    <p class="text-gray-600 text-base mb-6">Ready to move forward with your project?</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        
                        <a href="/contact-us" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center flex-1 px-6 py-3 rounded-lg text-white font-semibold bg-gradient-to-br from-[var(--color-primary-orange)] to-[var(--color-hover-orange)] hover:from-[var(--color-hover-orange)] hover:to-[var(--color-primary-orange)] hover:shadow-[0_25px_50px_-12px_rgba(0,0,0,0.3)] transition-all duration-500">
                            <i class="fas fa-envelope mr-2" aria-hidden="true"></i> Contact Us
                        </a>
                        <button type="button" id="save-estimate-btn" class="inline-flex items-center justify-center flex-1 px-6 py-3 rounded-lg border-2 border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                            <i class="fas fa-download mr-2" aria-hidden="true"></i> Save Estimate (PDF)
                        </button>
                    </div>
                </div>
            </div>
 
        </div>
 
        <!-- Wizard Navigation -->
        <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200" id="wizard-bottom-container">
            <button id="wizard-back-btn" class="inline-flex items-center px-6 py-3 rounded-lg border-2 border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors duration-200 hidden focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                <i class="fas fa-arrow-left mr-2" aria-hidden="true"></i> Back
            </button>
            
            <!-- Next Button -->
            <button id="wizard-next-btn" class="ml-auto inline-flex items-center px-8 py-3 rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 text-white font-medium hover:from-orange-600 hover:to-orange-700 transition-colors duration-200 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                <span class="button-content">Next</span>
                <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i>
            </button>

            <!-- Submit Button -->
            <button id="wizard-submit-btn" class="ml-auto inline-flex items-center px-8 py-3 rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 text-white font-medium hover:from-orange-600 hover:to-orange-700 transition-colors duration-200 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                <span class="button-content">Find Your Dream Home</span>
                <i class="fas fa-home ml-2" aria-hidden="true"></i>
            </button>
            
            <button class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 view-all-homes-btn focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                <i class="fas fa-arrow-left mr-2" aria-hidden="true"></i> Back to Gallery
            </button>
            <button class="hidden inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 back-to-featured-gallery-btn focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                <i class="fas fa-arrow-left mr-2" aria-hidden="true"></i> Back to Featured gallery
            </button>
            <button id="wizard-close-btn" class="inline-flex items-center px-6 py-3 rounded-lg border-2 border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors duration-200 hidden focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                Close
            </button>
        </div>
 
    </div>
 </div>