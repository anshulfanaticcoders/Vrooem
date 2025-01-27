<script setup>
// car overview icons import
import doorIcon from "../../assets/door.svg";
import luggageIcon from "../../assets/luggage.svg";
import fuelIcon from "../../assets/fuel.svg";
import transmisionIcon from "../../assets/transmision.svg";
import peopleIcon from "../../assets/people.svg";
import carbonIcon from "../../assets/carbon-emmision.svg";
import ageIcon from "../../assets/age.svg";
import enginepowerIcon from "../../assets/enginepower.svg";
import unlimitedKmIcon from "../../assets/unlimitedKm.svg";
import cancellationIcon from "../../assets/cancellationAvailable.svg";
import starIcon from "../../assets/stars.svg";
import MapPin from "../../assets/MapPin.svg";

import ShareIcon from "../../assets/ShareNetwork.svg";
import Heart from "../../assets/Heart.svg";
import carIcon from "../../assets/carIcon.svg";
import walkIcon from "../../assets/walking.svg";
import mileageIcon from "../../assets/mileageIcon.svg";
import pickupLocationIcon from "../../assets/pickupLocationIcon.svg";
import partnersIcon from "../../assets/partners.svg";
import infoIcon from "../../assets/WarningCircle.svg";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthenticatedHeaderLayout from "@/Layouts/AuthenticatedHeaderLayout.vue";

// Fetching Vehicle Details
import { usePage } from "@inertiajs/vue3";

const { props } = usePage(); // Get the props passed from the controller
const vehicle = ref(props.vehicle);
const currencies = ref([]); 

// Fetch currency data from the JSON file
const fetchCurrencies = async () => {
    try {
        const response = await axios.get('/api/currencies'); // Adjust the path as necessary
        currencies.value = response.data;
    } catch (error) {
        console.error('Error fetching currencies:', error);
    }
};

// Get currency symbol based on vendor's profile currency
const getCurrencySymbol = (currencyCode) => {
    const currency = currencies.value.find(c => c.code === currencyCode);
    return currency ? currency.symbol : ''; // Return symbol or empty string if not found
};

// Feature-Icon Mapping
const featureIconMap = {
    "Bluetooth": "/storage/icons/bluetooth.svg",
    "Music System": "/storage/icons/music.svg",
    "Toolkit": "/storage/icons/toolkit.svg",
    "USB Charger": "/storage/icons/usb.svg",
    "Key Lock": "/storage/icons/lock.svg",
    "Back Camera": "/storage/icons/camera.svg",
    "Voice Control": "/storage/icons/voiceControl.svg",
    "Navigation": "/storage/icons/gps-navigation.png",
};


// Map Script
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { onMounted } from 'vue'

// Add this after your existing vehicle ref declaration
let map = null

// Add this function before the template
const initMap = () => {
    if (!vehicle.value || !vehicle.value.latitude || !vehicle.value.longitude) {
        console.warn('No vehicle location data available');
        return;
    }

    // Initialize map
    map = L.map('map', {
        zoomControl: true,
        maxZoom: 18,
        minZoom: 4,
    }).setView([vehicle.value.latitude, vehicle.value.longitude], 15);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Create custom marker icon
    const customIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `
            <div class="marker-pin">
                
                <img src="${MapPin}" alt="Vehicle Location" />
            </div>
        `,
        iconSize: [50, 30],
        iconAnchor: [25, 15],
        popupAnchor: [0, -15]
    });

    // Add marker
    const marker = L.marker([vehicle.value.latitude, vehicle.value.longitude], {
        icon: customIcon
    })
        .bindPopup(`
            <div class="text-center">
                <p class="font-semibold">${vehicle.value.brand}</p>
                <p>${vehicle.value.location}</p>
            </div>
        `)
        .addTo(map);

    // Force a map refresh after a short delay
    setTimeout(() => {
        map.invalidateSize()
    }, 100)
}

// Add this in your script setup
onMounted(() => {
    initMap();
    fetchCurrencies();
});

import axios from "axios";
import { router } from "@inertiajs/vue3";
import Faq from "@/Components/Faq.vue";
import Footer from "@/Components/Footer.vue";
const form = ref({
    where: "",
    date_from: "",
    date_to: "",
    time_from: "",
    time_to: "",
    latitude: null,
    longitude: null,
    radius: 831867.4340914232,
});
const searchResults = ref([]);

const handleSearchInput = async () => {
    if (form.value.where.length < 3) return;

    try {
        const response = await axios.get(
            `/api/geocoding/autocomplete?text=${encodeURIComponent(
                form.value.where
            )}`
        );
        searchResults.value = response.data.features;
    } catch (error) {
        console.error("Error fetching locations:", error);
    }
};

const selectLocation = (result) => {
    form.value.where = result.properties?.label || "Unknown Location";
    form.value.latitude = result.geometry.coordinates[1];
    form.value.longitude = result.geometry.coordinates[0];
    searchResults.value = [];

    const latLng = [form.value.latitude, form.value.longitude];

};

const submit = () => {
    router.get("/s", form.value);
};

const getCurrentDate = () => {
    return new Date().toISOString().split('T')[0];
};
// Add these helper functions
const departureTimeOptions = [
    { value: '09:00', label: 'From 9 AM' },
    { value: '14:00', label: 'From 2 PM' },
];

const returnTimeOptions = [
    { value: '12:00', label: 'Before 12 PM' },
    { value: '20:00', label: 'Before 8 PM' },
];

// Function to update URL and session storage
const updateDateTimeSelection = () => {
    // Save to session storage
    sessionStorage.setItem('rentalDates', JSON.stringify({
        date_from: form.value.date_from,
        date_to: form.value.date_to,
        time_from: form.value.time_from,
        time_to: form.value.time_to
    }));

    // Update URL with new parameters
    const params = new URLSearchParams(window.location.search);
    params.set('date_from', form.value.date_from);
    params.set('date_to', form.value.date_to);
    params.set('time_from', form.value.time_from);
    params.set('time_to', form.value.time_to);

    // Update URL without reloading the page
    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.pushState({}, '', newUrl);
};

// Function to load saved dates from session storage
const loadSavedDates = () => {
    const savedDates = sessionStorage.getItem('rentalDates');
    if (savedDates) {
        const dates = JSON.parse(savedDates);
        form.value.date_from = dates.date_from || '';
        form.value.date_to = dates.date_to || '';
        form.value.time_from = dates.time_from || '';
        form.value.time_to = dates.time_to || '';
    }
};

// Call this in onMounted
onMounted(() => {
    loadSavedDates();
});


// getting user and user Profile data of current user

const user = ref(null);

const fetchUserProfile = async () => {
    try {
        // Make the request to fetch the current user's profile (no need to pass userId as it's dynamically fetched from auth)
        const response = await axios.get("/user"); // This endpoint fetches the current authenticated user's data

        if (response.data.status === "success") {
            user.value = response.data.data; // Store the user profile data
        } else {
            console.error("Failed to fetch user:", response.data.message);
        }
    } catch (error) {
        console.error("Error fetching user:", error);
    }
};

onMounted(fetchUserProfile);

// formatted joined date of vendor
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.getFullYear();
};


const validateRentalDetails = () => {
    if (!form.value.date_from) {
        alert("Please select a pickup date.");
        return false;
    }
    if (!form.value.time_from) {
        alert("Please select a pickup time.");
        return false;
    }
    if (!form.value.date_to) {
        alert("Please select a return date.");
        return false;
    }
    if (!form.value.time_to) {
        alert("Please select a return time.");
        return false;
    }
    return true;
};
const proceedToPayment = () => {
    // Validate rental details before proceeding
    if (!validateRentalDetails()) {
        return; // Stop the function if validation fails
    }

    // Proceed to payment page
    router.get(`/booking/${vehicle.value.id}`);
};
</script>

<template>

    <Head title="Single Car" />
    <AuthenticatedHeaderLayout />
    <main>
        <section>
            <div class="full-w-container py-customVerticalSpacing">
                <div class="flex gap-2 items-center mb-1">
                    <h4 class="font-medium">{{ vehicle?.brand }}</h4>
                    <span class="bg-[#f5f5f5] inline-block px-8 py-2 text-center rounded-[40px]">
                        {{ vehicle?.category.name }}
                    </span>
                </div>
                <div class="flex gap-2 items-center text-[1.25rem]">
                    <div class="car_ratings flex gap-2 items-center">
                    <img :src=starIcon alt="">
                        <span>5(1)</span>
                    </div>
                    <div class="dot_seperator"><strong>.</strong></div>
                    <div class="car_location">
                        <span>{{ vehicle?.location }}</span>
                    </div>
                </div>
                <div class="w-full mt-[1rem] flex gap-2">
                    <div class="primary-image w-[60%] max-h-[500px]">
                        <img v-if="vehicle?.images" :src="`/storage/${vehicle.images.find(
                            (image) => image.image_type === 'primary'
                        )?.image_path
                            }`" alt="Primary Image" class="w-full h-full object-cover rounded-lg" />
                    </div>

                    <!-- Display the gallery images -->
                    <div class="gallery w-[50%] grid grid-cols-2 gap-2 max-h-[245px]">
                        <div v-for="(image, index) in vehicle?.images.filter(
                            (image) => image.image_type === 'gallery'
                        )" :key="image.id" class="gallery-item">
                            <img :src="`/storage/${image.image_path}`" :alt="`Gallery Image ${index + 1}`"
                                class="w-full h-[245px] object-cover rounded-lg" />
                        </div>
                    </div>
                </div>
                <div class="flex justify-between mt-[4rem]">
                    <div class="column w-[50%]">
                        <div class="column flex flex-col gap-10">
                            <!-- Vehicle Features Section -->
                            <span class="text-[2rem] font-medium">Car Overview</span>
                            <div class="features grid grid-cols-4 gap-x-[2rem] gap-y-[2rem]">
                                <div class="feature-item items-center flex gap-3">
                                    <img :src="peopleIcon" alt="" class='w-[30px] h-[30px]' />
                                    <div class="flex flex-col">
                                        <span class="text-customLightGrayColor text-[1rem]">People</span>
                                        <span class="font-medium text-[1rem]">{{
                                            vehicle?.seating_capacity
                                            }}</span>
                                    </div>
                                </div>
                                <div class="feature-item items-center flex gap-3">
                                    <img :src="doorIcon" alt="" class='w-[30px] h-[30px]' />
                                    <div class="flex flex-col">
                                        <span class="text-customLightGrayColor text-[1rem]">Doors</span>
                                        <span class="font-medium text-[1rem]">{{
                                            vehicle?.number_of_doors
                                            }}</span>
                                    </div>
                                </div>
                                <div class="feature-item items-center flex gap-3">
                                    <img :src="luggageIcon" alt="" class='w-[30px] h-[30px]' />
                                    <div class="flex flex-col">
                                        <span class="text-customLightGrayColor text-[1rem]">Luggage</span>
                                        <span class="font-medium text-[1rem]">{{
                                            vehicle?.luggage_capacity
                                            }}</span>
                                    </div>
                                </div>
                                <div class="feature-item items-center flex gap-3">
                                    <img :src="transmisionIcon" alt="" class='w-[30px] h-[30px]' />
                                    <div class="flex flex-col">
                                        <span class="text-customLightGrayColor text-[1rem]">Transmission</span>
                                        <span class="font-medium capitalize">{{
                                            vehicle?.transmission
                                            }}</span>
                                    </div>
                                </div>
                                <div class="feature-item items-center flex gap-3">
                                    <img :src="fuelIcon" alt="" class='w-[30px] h-[30px]' />
                                    <div class="flex flex-col">
                                        <span class="text-customLightGrayColor text-[1rem]">Fuel Type</span>
                                        <span class="font-medium capitalize">{{
                                            vehicle?.fuel
                                            }}</span>
                                    </div>
                                </div>
                                <div class="feature-item items-center flex gap-3">
                                    <img :src="enginepowerIcon" alt="" class='w-[30px] h-[30px]' />
                                    <div class="flex flex-col">
                                        <span class="text-customLightGrayColor text-[1rem]">Horsepower</span>
                                        <span class="font-medium text-[1rem]">{{ vehicle?.horsepower }} hp</span>
                                    </div>
                                </div>
                                <div class="feature-item items-center flex gap-3">
                                    <img :src="carbonIcon" alt="" class='w-[30px] h-[30px]' />
                                    <div class="flex flex-col">
                                        <span class="text-customLightGrayColor text-[1rem]">Co2 Emission</span>
                                        <span class="font-medium text-[1rem]">{{ vehicle?.co2 }} (g/km)</span>
                                    </div>
                                </div>
                                <div class="feature-item items-center flex gap-3">
                                    <img :src="mileageIcon" alt="" class='w-[30px] h-[30px]' />
                                    <div class="flex flex-col">
                                        <span class="text-customLightGrayColor text-[1rem]">Mileage</span>
                                        <span class="font-medium text-[1rem]">{{ vehicle?.mileage }} km/d</span>
                                    </div>
                                </div>
                                <div class="feature-item items-center flex gap-3">
                                    <img :src="ageIcon" alt="" class='w-[30px] h-[30px]' />
                                    <div class="flex flex-col">
                                        <span class="text-customLightGrayColor text-[1rem]">Minimum Driving Age</span>
                                        <span class="font-medium text-[1rem]">21</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="features mt-[3rem]">
                            <span class="text-[2rem] font-medium">Features</span>
                            <div class="grid grid-cols-4 mt-[2rem] gap-y-[2rem]">
                                <div class="flex items-center gap-3" v-if="vehicle?.features" v-for="(feature, index) in JSON.parse(
                                    vehicle.features
                                )" :key="index">
                                    <img :src="featureIconMap[feature]" alt="Feature Icon"
                                        class="feature-icon w-[30px] h-[30px]" />
                                    {{ feature }}
                                </div>
                                <div v-else>
                                    <p>No features available.</p>
                                </div>
                            </div>
                        </div>

                        <div class=" mt-[3rem]">
                            <span class="text-[2rem] font-medium">Car Location</span>
                            <div class="gap-y-[2rem]">
                                {{ vehicle?.location }}
                            </div>
                            <div id="map" class="h-full rounded-lg mt-4"></div>
                        </div>

                        <div class="mt-[5rem]">
                            <span class="text-[2rem] font-medium">Banefits</span>
                            <div class="flex justify-between gap-5">
                                <div
                                    class=" flex justify-between gap-5 border-[1px] border-customPrimaryColor rounded-[0.75em] px-[1rem] py-[2rem]">
                                    <img :src=unlimitedKmIcon alt="">
                                    <div>
                                        <h4 class="text-customPrimaryColor text-[1.75rem] font-medium">Unlimited KMs
                                        </h4>
                                        <p class="text-customLightGrayColor">Endless kilometres with no extra charge</p>
                                    </div>
                                </div>
                                <div
                                    class=" flex justify-between gap-5 border-[1px] border-customPrimaryColor rounded-[0.75em] px-[1rem] py-[2rem]">
                                    <img :src=cancellationIcon alt="">
                                    <div>
                                        <h4 class="text-customPrimaryColor text-[1.75rem] font-medium">Cancellation
                                            Unavailable</h4>
                                        <p class="text-customLightGrayColor">This booking is non-refundable</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-[5rem]">
                            <span class="text-[2rem] font-medium">Meet Vehicle Vendor</span>
                            <div
                                class="mt-[2rem] flex gap-5 border-[1px] border-customPrimaryColor rounded-[0.75em] px-[1rem] py-[2rem]">
                                <img :src="user?.profile.avatar
                                    ? `/storage/${user?.profile.avatar}`
                                    : '/storage/avatars/default-avatar.svg'
                                    " alt="User Avatar" class="w-[100px] h-[100px] rounded-full object-cover" />
                                <div>
                                    <h4 class="text-customPrimaryColor text-[1.75rem] font-medium">
                                        {{ vehicle.user.first_name }} {{ vehicle.user.last_name }}
                                    </h4>
                                    <p class="text-customLightGrayColor">{{ vehicle.vendor_profile.about }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="column w-[40%]">
                        <div class="paymentInfoDiv p-5 sticky top-[3rem]">
                            <div class="flex items-center justify-between gap-3">
                                <h4>{{ vehicle?.brand }}</h4>
                                <span class="bg-[#f5f5f5] inline-block px-8 py-2 text-center rounded-[40px]">
                                    {{ vehicle?.category.name }}
                                </span>
                                <div class="icons flex items-center gap-3">
                                    <Link href="" class="w-full"><img :src="ShareIcon" alt="" /></Link>
                                    <Link href="" class="w-full"><img :src="Heart" alt="" /></Link>
                                </div>
                            </div>
                            <div>
                                <span>Hosted by
                                    <span class="vendorName uppercase">
                                        {{ vehicle?.user.first_name }}
                                        {{ vehicle?.user.last_name }}
                                    </span>
                                </span>
                            </div>
                            <div class="car_short_info mt-[1rem] flex gap-3">
                                <img :src="carIcon" alt="" />
                                <div class="features">
                                    <span class="text-[1.15rem] capitalize">
                                        {{ vehicle?.transmission }} .
                                        {{ vehicle?.fuel }} .
                                        {{ vehicle?.seating_capacity }} Seats
                                    </span>
                                </div>
                            </div>
                            <div class="extra_details flex gap-5 mt-[1rem]">
                                <div class="col flex gap-3">
                                    <img :src="walkIcon" alt="" /><span class="text-[1.15rem]">9.3 KM Away</span>
                                </div>
                                <div class="col flex gap-3">
                                    <img :src="mileageIcon" alt="" /><span class="text-[1.15rem]">{{ vehicle?.mileage }}
                                        km/d</span>
                                </div>
                            </div>

                            <div class="ratings"></div>

                            <div class="location mt-[2rem]">
                                <span class="text-[1.5rem] font-medium mb-[1rem] inline-block">Location</span>
                                <div class="col flex items-start gap-4">
                                    <img :src="pickupLocationIcon" alt="" />
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[1.25rem] text-medium">{{ vehicle?.location }}</span>
                                        <span>{{ vehicle?.created_at }}</span>
                                    </div>
                                </div>

                                <div class="edit mt-[1rem] border-[1px] rounded-[12px] border-[#2b2b2b40] py-[1rem]">
                                    <form @submit.prevent="submit"
                                        class="column px-[2rem] py-[1rem] rounded-tr-[20px] rounded-br-[20px] bg-white grid grid-cols-2">
                                        <div class="col col-span-2 flex flex-col justify-center">
                                        </div>

                                        <!-- Date Selection -->
                                        <div class="col px-5 flex flex-col justify-center">
                                            <label class="block text-sm mb-1 text-customLightGrayColor font-medium">
                                                Pick Up Date
                                            </label>
                                            <input type="date" v-model="form.date_from" :min="getCurrentDate()"
                                                @change="updateDateTimeSelection"
                                                class="p-2 rounded border border-customMediumBlackColor w-full text-customPrimaryColor" />
                                            <!-- Time Dropdown -->
                                            <label
                                                class="block text-sm mt-3 mb-1 text-customLightGrayColor font-medium">
                                                Departure Time
                                            </label>
                                            <select v-model="form.time_from" @change="updateDateTimeSelection"
                                                class="p-2 rounded border border-customMediumBlackColor w-full text-customPrimaryColor">
                                                <option value="">Select time</option>
                                                <option v-for="option in departureTimeOptions" :key="option.value"
                                                    :value="option.value">
                                                    {{ option.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col px-5 flex flex-col justify-center">
                                            <label class="block text-sm mb-1 text-customLightGrayColor font-medium">
                                                Return Date
                                            </label>
                                            <input type="date" v-model="form.date_to"
                                                :min="form.date_from || getCurrentDate()"
                                                @change="updateDateTimeSelection"
                                                class="p-2 rounded border border-gray-300 w-full text-customPrimaryColor" />
                                            <!-- Time Dropdown -->
                                            <label
                                                class="block text-sm mt-3 mb-1 text-customLightGrayColor font-medium">
                                                Return Time
                                            </label>
                                            <select v-model="form.time_to" @change="updateDateTimeSelection"
                                                class="p-2 rounded border border-customMediumBlackColor w-full text-customPrimaryColor">
                                                <option value="">Select time</option>
                                                <option v-for="option in returnTimeOptions" :key="option.value"
                                                    :value="option.value">
                                                    {{ option.label }}
                                                </option>
                                            </select>
                                        </div>
                                    </form>
                                </div>

                                <div class="pricing py-5">
                                    <div class="column flex items-center justify-between">
                                        <span class="text-[1.25rem]">Total Price</span>
                                        <div>
                                            <span class="text-customPrimaryColor text-[1.875rem] font-medium">
                                {{ getCurrencySymbol(vehicle.vendor_profile.currency) }}{{ vehicle?.price_per_day }}
                            </span>
                                            
                                            <br />
                                            <span class="flex gap-3">incl. VAT
                                                <img :src="infoIcon" alt="" /></span>
                                        </div>
                                    </div>
                                    <div class="column mt-[2rem]">
                                        <button @click="proceedToPayment"
                                            class="button-primary block text-center p-5 w-full">
                                            Proceed to Pay
                                        </button>
                                    </div>
                                    <div
                                        class="column text-center mt-[2rem] flex flex-col justify-center items-center gap-5">
                                        <p>Guaranteed safe & secure checkout</p>
                                        <img :src="partnersIcon" alt="" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-customVerticalSpacing">
            <Faq />
        </section>

        <section class="full-w-container">

        </section>

        <section class="full-w-container py-customVerticalSpacing">
            <div
                class="mt-[2rem] flex items-center justify-center gap-5 border-[1px] border-customPrimaryColor rounded-[0.75em] px-[1rem] py-[2rem]">
                <div class="flex flex-col items-center gap-5 w-[50%]">
                    <img :src="user?.profile.avatar
                        ? `/storage/${user?.profile.avatar}`
                        : '/storage/avatars/default-avatar.svg'
                        " alt="User Avatar" class="w-[100px] h-[100px] rounded-full object-cover" />
                    <h4 class="text-customPrimaryColor text-[1.75rem] font-medium">
                        {{ vehicle.user.first_name }} {{ vehicle.user.last_name }}
                    </h4>
                    <span>On VROOEM since {{ formatDate(vehicle.user.created_at) }}</span>
                    <div class="flex justify-between w-full">
                        <div class="col flex flex-col items-center">
                            <p class="capitalize text-[1.5rem] text-customPrimaryColor font-bold">{{
                                vehicle?.vendor_profile_data.status }}</p>
                            <span class="text-customLightGrayColor">Verification Status</span>
                        </div>
                        <div class="col flex flex-col items-center">
                            <p class="capitalize text-[1.5rem] text-customPrimaryColor font-bold">{{
                                vehicle?.vendor_profile_data.status }}</p>
                            <span class="text-customLightGrayColor">Verification Status</span>
                        </div>
                        <div class="col flex flex-col items-center">
                            <p class="capitalize text-[1.5rem] text-customPrimaryColor font-bold">{{
                                vehicle?.vendor_profile_data.status }}</p>
                            <span class="text-customLightGrayColor">Verification Status</span>
                        </div>
                        <div class="col flex flex-col items-center">
                            <p class="capitalize text-[1.5rem] text-customPrimaryColor font-bold">{{
                                vehicle?.vendor_profile_data.status }}</p>
                            <span class="text-customLightGrayColor">Verification Status</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <Footer />
</template>

<style>
.overview .col:not(:last-child) {
    border-bottom: 1px solid #2b2b2b;
}

.overview .col {
    padding: 2rem;
}

.paymentInfoDiv {
    border-radius: 0.75rem;
    border: 0.5px solid #ede7e7;
    background: #fff;
    box-shadow: 0px 0px 32px 0px rgba(196, 196, 196, 0.24);
}

.galley-item {
    border-radius: 0.75rem;
    border: 0.5px solid #ede7e7;
    background: #fff;
    box-shadow: 0px 0px 32px 0px rgba(196, 196, 196, 0.24);
}

@import 'leaflet/dist/leaflet.css';

.marker-pin {
    width: auto;
    min-width: 50px;
    height: 30px;
    background: white;
    border: 2px solid #666;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.marker-pin span {
    color: black;
    font-weight: bold;
    font-size: 12px;
    padding: 0 8px;
}

.custom-div-icon {
    background: none;
    border: none;
}

#map {
    height: 410px;
    width: 100%;
}
</style>
