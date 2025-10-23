import sys
import pickle

# Load model
with open("ml_model/model.pkl", "rb") as f:
    model = pickle.load(f)

# Get inputs
grades = float(sys.argv[1])
exam = float(sys.argv[2])
interview = float(sys.argv[3])

# Prepare data for prediction
X = [[grades, exam, interview]]
prediction = model.predict(X)[0]

# Print result (echoed sa PHP)
print("Eligible" if prediction == 1 else "Not Eligible")
