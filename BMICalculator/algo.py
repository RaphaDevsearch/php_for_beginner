def calculateBMI(weight, height):
  if height <= 0 or weight <= 0:
    print("Height and weight must be greater than zero.")
    return None
  
  return weight / (height ** 2)



def categorizeBMI(bmi):
  categories = {
    "Underweight"   : (0, 18.5),
    "Normal weight" : (18.5, 24.9),
    "Overweight"    : (25, 29.9),
    "Obesity"       : (30, float('inf'))
  }
  
  for category, (lower, upper) in categories.items():
    if lower <= bmi < upper:
      return category
  
  return "Invalid BMI"