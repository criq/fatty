# Fatty Library v2 - AI Agent Notes

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document summarizes key technical details, architecture, and conventions for the `criq/fatty` library v2 to streamline development tasks by AI agents.

---

## 1. Quick Start & Overview

### 1.1. Library Overview
- **Type:** PHP library for advanced caloric and nutritional calculations
- **Purpose:** Comprehensive body composition analysis, dietary planning, and pregnancy management
- **Namespace:** `Fatty\`
- **Dependencies:** `criq/katu` ^4, `criq/effekt` ^4
- **Location:** `v2/vendor/criq/fatty/`
- **Version:** v2 (significantly enhanced from v1)

### 1.2. Core Functionality
- **Body Composition Analysis:** BMI, body fat percentage, body types, fitness levels
- **Metabolic Calculations:** BMR, TDEE, energy expenditure with multiple strategies
- **Nutritional Planning:** Advanced diet approaches including DIA150, DiaMama
- **Goal Management:** Weight change vectors with strategy-based calculations
- **Pregnancy Support:** Comprehensive pregnancy and breastfeeding management
- **Child Management:** Multiple children tracking with individual breastfeeding modes

### 1.3. Key Entry Points
- **Main Calculator:** `Fatty\Calculator` - Enhanced calculation engine with validation
- **Factory Methods:** `Calculator::createFromRequest(ServerRequestInterface $request)`
- **Response Generation:** `$calculator->getRestResponse()` - RESTful API responses

---

## 2. Core Architecture

### 2.1. Enhanced Calculator Class
The `Calculator` class is significantly enhanced in v2:

```php
class Calculator implements RestResponseInterface
{
    protected $activity;           // Physical activity level
    protected $birthday;          // User's birth date
    protected $bodyFatPercentage; // Body fat percentage
    protected $diet;              // Diet approach and settings
    protected $gender;            // Male/Female with pregnancy support
    protected $goal;              // Weight goal and vector
    protected $proportions;       // Body measurements
    protected $referenceTime;     // Reference time for calculations
    protected $sportDurations;    // Exercise duration tracking
    protected $strategy;          // Calculation strategy (DiaMama/Zivot20)
    protected $units = "kcal";    // Energy units (kJ/kcal)
    protected $weight;            // Current weight
    protected $weights;           // Weight collection for history
}
```

### 2.2. Strategy Pattern
New strategy system for different calculation approaches:

```php
abstract class Strategy
{
    abstract public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetricResult;
    abstract public function calcWeightGoalQuotient(Calculator $calculator): AmountMetricResult;
    abstract public function getBodyMassIndexWeight(Calculator $calculator): ?Weight;
}
```

**Available Strategies:**
- **DiaMama:** Pregnancy-specific calculations with BMI-based weight goals
- **Zivot20:** Standard calculations for non-pregnant users

### 2.3. Enhanced Validation System
Comprehensive validation using `Katu\Tools\Validation`:

```php
public static function createFromRequest(ServerRequestInterface $request): Validation
{
    $validations = new ValidationCollection;
    $calculator = new static;

    // Validates all input parameters with detailed error reporting
    // Returns Validation object with errors or Calculator instance
}
```

---

## 3. Pregnancy and Child Management

### 3.1. Pregnancy System
Comprehensive pregnancy tracking:

```php
class Pregnancy
{
    protected $childbirthDate;        // Expected delivery date
    protected $numberOfChildren = 1;  // Number of children
    protected $weightBeforePregnancy; // Pre-pregnancy weight

    public function getCurrentWeek(Time $referenceTime): ?Week
    public function getCurrentTrimester(Time $referenceTime): ?Trimester
    public function getIsPregnant(Time $referenceTime): bool
}
```

### 3.2. Child Management
Support for multiple children with individual breastfeeding modes:

```php
class Child
{
    protected $birthday;
    protected $breastfeedingMode; // Full, Partial, None
}

class ChildCollection extends \ArrayObject
{
    // Collection of Child objects
}
```

### 3.3. Breastfeeding Modes
- **Full:** Complete breastfeeding
- **Partial:** Mixed feeding
- **None:** No breastfeeding

---

## 4. Enhanced Metrics System

### 4.1. Metric Result Architecture
New comprehensive metric result system:

```php
abstract class MetricResult implements MetricResultCollectionAddable, RestResponseInterface
{
    protected $errors;      // ErrorCollection for validation errors
    protected $formatted;   // Formatted output
    protected $formula;     // Calculation formula
    protected $metric;      // Metric definition
    protected $result;      // Actual result value
}
```

### 4.2. Metric Result Types
- **AmountMetricResult:** Numeric values
- **QuantityMetricResult:** Values with units
- **StringMetricResult:** Text-based results
- **BooleanMetricResult:** True/false values
- **ArrayMetricResult:** Array data

### 4.3. Metric Result Collection
```php
class MetricResultCollection extends \ArrayObject
{
    public function filterByMetric(Metric $metric): MetricResultCollection
    public function filterByCode(string $code): MetricResultCollection
    public function getFirst(): ?MetricResult
    public function add(MetricResult $metricResult): MetricResultCollection
}
```

---

## 5. Diet and Nutrition System

### 5.1. Enhanced Diet Approaches
Extended approach system with new implementations:

- **Standard:** Balanced macronutrient distribution
- **Keto:** Very low carbohydrate approach
- **LowCarb:** Reduced carbohydrate intake
- **LowEnergy:** Calorie-restricted approach
- **LowEnergyTransition:** Gradual calorie reduction
- **Mediterranean:** Mediterranean diet principles
- **DIA150:** Diabetes-specific 150g carb approach
- **DiaMama/Standard:** Pregnancy-specific standard diet
- **DiaMama/LowCarb:** Pregnancy-specific low carb diet
- **DiaMama/HighCarb:** Pregnancy-specific high carb diet

### 5.2. DIA150 Approach
Specialized approach for diabetes management:

```php
class DIA150 extends \Fatty\Approach
{
    const CARBS_DEFAULT = 150;
    const CODE = "DIA150";
    const LABEL_DECLINATED = "dietu DIA150";

    // Fixed 150g carbohydrate intake
    // Remaining energy distributed to fats
}
```

### 5.3. Enhanced Nutrient Calculations
More sophisticated macronutrient distribution with pregnancy considerations.

---

## 6. Gender-Specific Enhancements

### 6.1. Enhanced Female Gender
Extended female gender class with pregnancy support:

```php
class Female extends \Fatty\Gender
{
    protected $pregnancy;     // Pregnancy object
    protected $children;      // ChildCollection
    protected $breastfeeding; // Breastfeeding status

    public function getIsPregnant(Calculator $calculator): bool
    public function getIsNewMother(Calculator $calculator): bool
    public function getPregnancy(): ?Pregnancy
    public function getChildren(): ChildCollection
}
```

### 6.2. Pregnancy-Specific Calculations
- BMI calculated from pre-pregnancy weight
- Specialized weight goal quotients based on pre-pregnancy BMI
- Pregnancy week and trimester tracking
- Breastfeeding energy adjustments

---

## 7. Error Handling and Validation

### 7.1. Enhanced Error System
Comprehensive error handling with specific error types:

```php
// Missing parameter errors
class MissingWeightError extends Error
class MissingGenderError extends Error
class MissingPregnancyError extends Error

// Invalid value errors
class InvalidWeightException extends FattyException
class InvalidGenderException extends FattyException
```

### 7.2. Validation Integration
Tight integration with Katu validation system:

```php
$weightValidation = Weight::validateWeight(new UserInput("weight", $params["weight"]));
$validations[] = $weightValidation;

if (!$weightValidation->hasErrors()) {
    $calculator->setWeight($weightValidation->getResponse());
}
```

---

## 8. REST API Integration

### 8.1. REST Response Interface
Full REST API support:

```php
class Calculator implements RestResponseInterface
{
    public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
    {
        // Returns comprehensive calculation results
        // Includes all metrics with errors, formulas, and formatted values
    }
}
```

### 8.2. Request-Based Creation
Direct creation from HTTP requests:

```php
$validation = Calculator::createFromRequest($request);
if ($validation->hasErrors()) {
    // Handle validation errors
} else {
    $calculator = $validation->getResponse();
    $results = $calculator->getRestResponse($request);
}
```

---

## 9. Key Calculation Methods

### 9.1. Body Composition
- `calcBodyMassIndex()`: BMI with strategy-based weight selection
- `calcWaistHipRatio()`: WHR calculation
- `calcBodyFatPercentage()`: Body fat percentage
- `calcFatFreeMass()`: Lean body mass
- `calcActiveBodyMassWeight()`: Active body mass
- `calcIsOverweight()`: Overweight status

### 9.2. Metabolic
- `calcBasalMetabolicRate()`: BMR with strategy selection
- `calcTotalDailyEnergyExpenditure()`: TDEE calculation
- `calcWeightGoalEnergyExpenditure()`: Strategy-based energy goals
- `calcReferenceDailyIntake()`: Complete daily intake recommendations

### 9.3. Pregnancy-Specific
- `calcPregnancyWeek()`: Current pregnancy week
- `calcPregnancyTrimester()`: Current trimester
- `calcWeightGoalQuotient()`: Strategy-based weight goals

### 9.4. Nutritional
- `calcGoalNutrients()`: Macronutrient targets
- `calcSportProteinCoefficient()`: Protein needs based on activity

---

## 10. Usage Patterns

### 10.1. Request-Based Usage
```php
$validation = Calculator::createFromRequest($request);
if ($validation->hasErrors()) {
    return $validation->getRestResponse($request);
}

$calculator = $validation->getResponse();
$results = $calculator->getRestResponse($request);
```

### 10.2. Direct Usage
```php
$calculator = new Calculator();
$calculator->setGender(new Genders\Female());
$calculator->setWeight(new Weight(new Amount(70), 'kg'));
$calculator->getGender()->setPregnancy(new Pregnancy(new Time('2024-06-01')));

$bmi = $calculator->calcBodyMassIndex();
$tdee = $calculator->calcTotalDailyEnergyExpenditure();
```

### 10.3. Strategy Selection
```php
// Automatic strategy selection based on gender and pregnancy status
$strategy = $calculator->getStrategy();

// Manual strategy selection
$calculator->setStrategy(new Strategies\DiaMama());
```

---

## 11. Configuration and Constants

### 11.1. Energy Conversion
- **Cal to J ratio:** 4.128
- **Base energy unit:** Joules (J)
- **Supported units:** J, kJ, cal, kcal
- **Default units:** kcal (changed from kJ in v1)

### 11.2. Pregnancy Constants
- **Pregnancy duration:** 280 days (40 weeks)
- **Trimester 1:** Weeks 1-13
- **Trimester 2:** Weeks 14-26
- **Trimester 3:** Weeks 27-40

### 11.3. DiaMama Strategy Constants
- **BMI ≤ 19:** Weight goal quotient 1.1 (weight gain)
- **BMI 19.1-24.9:** Weight goal quotient 1.0 (maintenance)
- **BMI 25-29.9:** Weight goal quotient 0.93 (light weight loss)
- **BMI ≥ 30:** Weight goal quotient 0.9 (weight loss)

---

## 12. Development Guidelines

### 12.1. Adding New Strategies
1. Extend `Strategy` abstract class
2. Implement required methods
3. Add strategy selection logic in `Calculator::getStrategy()`

### 12.2. Adding New Metrics
1. Create metric class extending `Metric`
2. Create metric result class extending appropriate `MetricResult`
3. Add calculation method to `Calculator`
4. Include in `getRestResponse()` method

### 12.3. Adding New Diet Approaches
1. Extend `Approach` abstract class
2. Implement `calcGoalNutrients()` method
3. Define approach constants
4. Add to approach validation

---

## 13. Key File Locations

### 13.1. Core Classes
- **Calculator:** `src/Calculator.php` - Main calculation engine
- **Strategy:** `src/Strategy.php` - Strategy pattern base
- **Pregnancy:** `src/Pregnancy.php` - Pregnancy management

### 13.2. Strategies
- **DiaMama:** `src/Strategies/DiaMama.php` - Pregnancy strategy
- **Zivot20:** `src/Strategies/Zivot20.php` - Standard strategy

### 13.3. Metrics System
- **MetricResult:** `src/Metrics/MetricResult.php` - Base result class
- **MetricResultCollection:** `src/Metrics/MetricResultCollection.php` - Collection management

### 13.4. Diet Approaches
- **DIA150:** `src/Approaches/DIA150.php` - Diabetes approach
- **DiaMama:** `src/Approaches/DiaMama/` - Pregnancy approaches

### 13.5. Pregnancy System
- **Pregnancy:** `src/Pregnancy.php` - Main pregnancy class
- **Week:** `src/Pregnancy/Week.php` - Pregnancy week tracking
- **Trimester:** `src/Pregnancy/Trimester.php` - Trimester tracking

---

## 14. Integration with v2 Application

### 14.1. User Model Integration
```php
// In User model
use Fatty\Calculator;
use Fatty\TimeWeight;
use Fatty\Weight;
use Fatty\WeightCollection;

// Calculator integration for user calculations
$calculator = new Calculator();
$calculator->setGender($this->getGender());
$calculator->setWeight($this->getWeight());
```

### 14.2. API Controllers
```php
// In API controllers
$validation = Calculator::createFromRequest($request);
if ($validation->hasErrors()) {
    return $validation->getRestResponse($request);
}

$calculator = $validation->getResponse();
return $calculator->getRestResponse($request);
```

### 14.3. Report Generation
```php
// In report classes
$calculator = new Calculator();
// ... set parameters
$results = $calculator->getRestResponse();
// Process results for reports
```

---

## 15. Performance Considerations

### 15.1. Calculation Efficiency
- Lazy loading of complex calculations
- Strategy-based calculation optimization
- Efficient error collection and reporting

### 15.2. Memory Management
- Lightweight value objects
- Efficient collection handling
- Minimal object creation overhead

### 15.3. Validation Performance
- Early validation failure detection
- Efficient error collection
- Minimal redundant calculations

---

## 16. Testing and Validation

### 16.1. Input Validation
- Comprehensive parameter validation
- Type checking and range validation
- Detailed error reporting with field names

### 16.2. Calculation Verification
- Formulas included in metric responses
- Unit conversion accuracy
- Strategy-specific calculation validation

### 16.3. Error Handling
- Graceful degradation for missing parameters
- Detailed error messages with context
- Validation collection for multiple errors

---

## 17. Quick Reference

### 17.1. Common Patterns
```php
// Request-based creation
$validation = Calculator::createFromRequest($request);
$calculator = $validation->getResponse();

// Strategy selection
$strategy = $calculator->getStrategy();

// Pregnancy support
$calculator->getGender()->setPregnancy(new Pregnancy($childbirthDate));

// Results
$results = $calculator->getRestResponse($request);
```

### 17.2. Key Constants
- **Energy base unit:** `Energy::BASE_UNIT = "J"`
- **Default units:** `Calculator::units = "kcal"`
- **Pregnancy duration:** `Pregnancy::DURATION = 280 days`

### 17.3. Important Methods
- `Calculator::createFromRequest()` - Request-based factory
- `Calculator::getRestResponse()` - Complete REST results
- `Calculator::getStrategy()` - Strategy selection
- `Pregnancy::getCurrentWeek()` - Pregnancy week calculation

---

## 18. Migration from v1

### 18.1. Key Changes
- **Validation:** New validation system with detailed error reporting
- **Strategies:** Strategy pattern for different calculation approaches
- **Metrics:** Enhanced metric system with result objects
- **Pregnancy:** Comprehensive pregnancy and child management
- **REST API:** Full REST API integration
- **Units:** Default units changed from kJ to kcal

### 18.2. Breaking Changes
- Method signatures changed for validation
- New error handling system
- Enhanced metric result structure
- Strategy-based calculations

### 18.3. Migration Steps
1. Update validation calls to use new system
2. Implement strategy selection logic
3. Update error handling for new error types
4. Modify result processing for new metric structure
5. Add pregnancy support if needed
