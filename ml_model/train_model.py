import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import classification_report
from joblib import dump

# Load & clean
df = pd.read_csv("Kanluran_scholars_dataset.csv")
df.columns = [col.strip().lower().replace(" ", "_") for col in df.columns]
df = df.drop_duplicates().dropna()
df['admission_status'] = df['admission_status'].str.strip().str.lower()
df = df[df['admission_status'].isin(['accepted', 'rejected', 'waitlisted'])]

# Binary target
df['scholarship_eligibility'] = df['admission_status'].apply(lambda x: 1 if x == 'accepted' else 0)

# Features and upsampling
X = df[['gpa', 'exam_results']]
y = df['scholarship_eligibility']

df_majority = df[df['scholarship_eligibility'] == 0]
df_minority = df[df['scholarship_eligibility'] == 1]
df_minority_upsampled = df_minority.sample(n=len(df_majority), replace=True, random_state=42)
df_balanced = pd.concat([df_majority, df_minority_upsampled])

X = df_balanced[['gpa', 'exam_results']]
y = df_balanced['scholarship_eligibility']

# Scale
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Train model
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_scaled, y)

# Save
dump(model, "scholarship_eligibility_model_rf.joblib")
dump(scaler, "scholarship_scaler_rf.joblib")

# Evaluate
y_pred = model.predict(X_scaled)
print("\n✅ Training Complete:")
print(classification_report(y, y_pred, target_names=["Not Eligible", "Eligible"]))