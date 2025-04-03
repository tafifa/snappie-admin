# Class Diagram Documentation

This document provides a detailed description of the class diagram for the project.

---

## Class: User

**Attributes:**  
- **userId:** String  
- **name:** String  
- **email:** String  
- **profilePicture:** String  
- **dateJoined:** Date  
- **points:** int  
- **coin:** int  

**Methods:**  
- `register(googleData, userData): User`  
- `login(credentials): User`  
- `updateProfile(profileData): void`  
- `getProfile(): UserProfile`  
- `resetMonthlyPoints(): void`  
- `addCoin(amount: int): void`  
- `deductCoin(amount: int): void`  

---

## Class: UserStatistics

**Attributes:**  
- **userId:** String  
- **totalPoints:** int  
- **totalCoins:** int  
- **totalChallenges:** int  
- **totalAchievements:** int  
- **totalMissions:** int  
- **totalReviews:** int  
- **totalUpvotes:** int  

**Methods:**  
- `updateStatistics(statData): void`  
- `getStatistics(): StatisticsData`  
- `incrementStat(field: String, value: int): void`  
- `resetMonthlyStats(): void`  

---

## Class: TravelPlan

**Attributes:**  
- **planId:** String  
- **userId:** String  
- **name:** String  
- **isDone:** boolean  

**Methods:**  
- `addPlace(placeId: String): void`  
- `removePlace(placeId: String): void`  
- `getPlan(): PlanDetails`  
- `markAsDone(): void`  
- `updatePlan(details: PlanUpdateData): void`  

---

## Class: TravelPlanPlace

**Attributes:**  
- **travelPlanId:** String  
- **placeId:** String  
- **sequence:** int  

**Methods:**  
- `updateSequence(newSequence: int): void`  
- `getSequence(): int`  

---

## Class: Place

**Attributes:**  
- **placeId:** String  
- **name:** String  
- **location:** Geopoint  
- **rating:** float  
- **description:** String  
- **images:** List<String>  
- **category:** List<String>  
- **tags:** List<String>  
- **isAvailable:** boolean  

**Methods:**  
- `calculateRating(): float`  
- `addReview(reviewData: ReviewData): void`  
- `getReviews(): List<Review>`  
- `updateAvailability(status: boolean): void`  
- `addTag(tag: String): void`  
- `removeTag(tag: String): void`  
- `updateImages(imageList: List<String>): void`  

---

## Class: Category

**Attributes:**  
- **categoryId:** String  
- **name:** String  

**Methods:**  
- `getPlaces(): List<Place>`  
- `addPlace(place: Place): void`  
- `updateCategory(details: CategoryUpdateData): void`  

---

## Class: Review

**Attributes:**  
- **reviewId:** String  
- **userId:** String  
- **placeId:** String  
- **rating:** float  
- **content:** String  
- **images:** List<String>  
- **upvotes:** int  
- **date:** Date  

**Methods:**  
- `createReview(reviewData: ReviewData): Review`  
- `updateReview(updatedData: ReviewData): void`  
- `getReview(): Review`  
- `getPhotos(): List<String>`  
- `upvoteReview(): void`  
- `removeUpvote(): void`  

---

## Class: Mission

**Attributes:**  
- **missionId:** String  
- **placeId:** String  
- **name:** String  
- **description:** String  
- **pointsReward:** int  
- **coinReward:** int  

**Methods:**  
- `submitPhoto(photo: String, location: Geopoint): void`  
- `getMissionDetails(): Mission`  
- `isAvailable(): boolean`  
- `updateRewards(newPoints: int, newCoin: int): void`  

---

## Class: UserMission

**Attributes:**  
- **userId:** String  
- **missionId:** String  
- **completedAt:** Date  
- **imageTaken:** String  

**Methods:**  
- `completeMission(): void`  
- `getCompletionStatus(): boolean`  
- `resubmitMission(photo: String): void`  

---

## Class: Challenge

**Attributes:**  
- **challengeId:** String  
- **name:** String  
- **description:** String  
- **isActive:** boolean  
- **pointsReward:** int  
- **coinReward:** int  

**Methods:**  
- `checkUserProgress(userId: String): ProgressData`  
- `activateChallenge(): void`  
- `deactivateChallenge(): void`  
- `updateRewards(newPoints: int, newCoin: int): void`  
- `getChallengeDetails(): Challenge`  

---

## Class: UserChallenge

**Attributes:**  
- **userId:** String  
- **challengeId:** String  
- **completedAt:** Date  
- **progress:** int  

**Methods:**  
- `updateProgress(newProgress: int): void`  
- `markChallengeComplete(): void`  
- `getChallengeStatus(): String`  

---

## Class: Achievement

**Attributes:**  
- **achievementId:** String  
- **name:** String  
- **description:** String  
- **level:** int  

**Methods:**  
- `getDetails(): Achievement`  
- `updateAchievement(details: AchievementUpdateData): void`  

---

## Class: UserAchievement

**Attributes:**  
- **userId:** String  
- **achievementId:** String  
- **currentLevel:** int  
- **progress:** int  
- **completedAt:** Date  

**Methods:**  
- `checkProgress(): int`  
- `unlockAchievement(): void`  
- `updateProgress(value: int): void`  

---

## Class: Admin

**Attributes:**  
- **adminId:** String  
- **name:** String  
- **email:** String  
- **password:** String  

**Methods:**  
- `login(credentials): Admin`  
- `manageUser(userId: String, action: String): void`  
- `managePlace(placeId: String, action: String): void`  
- `manageMission(missionId: String, action: String): void`  
- `getAdminDashboard(): DashboardData`  

---

## Class: Leaderboard

**Attributes:**  
- **leaderboardId:** String  
- **startDate:** Date  
- **endDate:** Date  
- **lastUpdated:** Date  

**Methods:**  
- `getRankings(): List<UserRanking>`  
- `updateRankings(): void`  
- `refreshLeaderboard(): void`  
- `getLeaderboardForPeriod(startDate: Date, endDate: Date): List<UserRanking>`  
