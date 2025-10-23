# training.py
import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler, OneHotEncoder
from sklearn.compose import ColumnTransformer
from sklearn.pipeline import Pipeline
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
from joblib import dump
import os

class ScholarshipModel:
    def __init__(self, csv_path="scholarship.csv", model_path="scholarship_eligibility_model_rf.joblib"):
        self.csv_path = csv_path
        self.model_path = model_path
        self.required_columns = [
            "gpa",
            "exam_results", 
            "number_of_dependents",
            "parent_employment_status",
            "admission_status"
        ]

    def load_data(self):
        """Load CSV and validate required columns"""
        try:
            # Check if file exists
            if not os.path.exists(self.csv_path):
                raise FileNotFoundError(f"CSV file not found: {self.csv_path}")
            
            df = pd.read_csv(self.csv_path)
            print(f"✅ Data loaded successfully. Shape: {df.shape}")
            
            # Clean column names (strip spaces and convert to lowercase)
            df.columns = [col.strip().lower().replace(" ", "_") for col in df.columns]
            print(f"📋 Columns found: {list(df.columns)}")
            
            # Check for required columns
            missing = set(self.required_columns) - set(df.columns)
            if missing:
                raise ValueError(f"Missing required columns: {missing}")
            
            # Clean data
            initial_count = len(df)
            df = df.drop_duplicates().dropna()
            final_count = len(df)
            
            if final_count < initial_count:
                print(f"🧹 Cleaned data: removed {initial_count - final_count} rows with duplicates/missing values")
            
            print(f"📊 Final dataset shape: {df.shape}")
            return df
            
        except Exception as e:
            print(f"❌ Error loading data: {e}")
            raise

    def preprocess(self, df):
        """Create target variable and split features"""
        # Create target variable based on your original logic
        df['scholarship_eligibility'] = df.apply(
            lambda row: 1 if (
                float(row['gpa']) <= 2.0 and 
                float(row['exam_results']) >= 80 and 
                int(row['number_of_dependents']) <= 2 and 
                str(row['parent_employment_status']).strip().lower() == "unemployed"
            ) else 0,
            axis=1
        )
        
        # Display target distribution
        eligibility_counts = df['scholarship_eligibility'].value_counts()
        print(f"🎯 Target distribution:")
        print(f"   Eligible (1): {eligibility_counts.get(1, 0)} students")
        print(f"   Not Eligible (0): {eligibility_counts.get(0, 0)} students")
        
        # Check if we have both classes
        if len(eligibility_counts) == 1:
            print("⚠️ WARNING: Only one class found in target variable!")
            print("💡 This means all students have the same eligibility status")
            print("💡 The model will still train but predictions will be biased")
        
        # Features and target
        X = df[["gpa", "exam_results", "number_of_dependents", "parent_employment_status"]]
        y = df["scholarship_eligibility"]
        
        return X, y

    def build_pipeline(self):
        """Create preprocessing + model pipeline"""
        numeric_features = ["gpa", "exam_results", "number_of_dependents"]
        categorical_features = ["parent_employment_status"]

        numeric_transformer = StandardScaler()
        categorical_transformer = OneHotEncoder(handle_unknown="ignore")

        preprocessor = ColumnTransformer(
            transformers=[
                ("num", numeric_transformer, numeric_features),
                ("cat", categorical_transformer, categorical_features),
            ]
        )

        model = RandomForestClassifier(
            n_estimators=100,  # Reduced for small datasets
            random_state=42,
            class_weight="balanced",
            max_depth=5,  # Reduced for small datasets
            min_samples_split=2,
            min_samples_leaf=1
        )

        pipeline = Pipeline(steps=[
            ("preprocessor", preprocessor),
            ("classifier", model)
        ])

        return pipeline

    def evaluate_model(self, model, X_test, y_test):
        """Comprehensive model evaluation that handles single-class cases"""
        try:
            y_pred = model.predict(X_test)
            
            print("\n📊 MODEL EVALUATION:")
            print("=" * 40)
            
            # Accuracy
            acc = accuracy_score(y_test, y_pred)
            print(f"✅ Test Accuracy: {acc:.4f}")
            
            # Get unique classes in test set
            unique_classes = np.unique(y_test)
            target_names = []
            
            if 0 in unique_classes:
                target_names.append("Not Eligible")
            if 1 in unique_classes:
                target_names.append("Eligible")
            
            # Classification report (handles single-class case)
            print("\n📈 Classification Report:")
            if len(unique_classes) == 2:
                print(classification_report(y_test, y_pred, target_names=target_names))
            else:
                print(f"Single class present: {target_names[0]}")
                print(f"All predictions will be: {target_names[0]}")
            
            # Confusion matrix
            cm = confusion_matrix(y_test, y_pred)
            print(f"🎯 Confusion Matrix:")
            if len(unique_classes) == 2:
                print(f"   True Negatives:  {cm[0, 0]}")
                print(f"   False Positives: {cm[0, 1]}")
                print(f"   False Negatives: {cm[1, 0]}")
                print(f"   True Positives:  {cm[1, 1]}")
            else:
                print(f"   All samples classified as: {target_names[0]}")
                print(f"   Count: {cm[0, 0]}")
                
        except Exception as e:
            print(f"⚠️ Evaluation limited due to: {e}")

    def train(self):
        """Train model and save"""
        try:
            print("🎓 STARTING SCHOLARSHIP MODEL TRAINING")
            print("=" * 50)
            
            # Load and preprocess data
            df = self.load_data()
            X, y = self.preprocess(df)
            
            # Check if we have enough data
            if len(df) < 5:
                print("❌ Insufficient data for training (need at least 5 samples)")
                return False
            
            # Handle single-class case
            unique_classes = y.unique()
            if len(unique_classes) == 1:
                print("⚠️ Training with single class dataset - model will predict only one outcome")
                print(f"💡 All students will be classified as: {'Eligible' if unique_classes[0] == 1 else 'Not Eligible'}")
            
            # Adjust test size for small datasets
            test_size = 0.2 if len(X) >= 10 else 0.3
            
            # Split data (without stratification if single class)
            if len(unique_classes) == 2:
                X_train, X_test, y_train, y_test = train_test_split(
                    X, y, test_size=test_size, random_state=42, stratify=y
                )
            else:
                X_train, X_test, y_train, y_test = train_test_split(
                    X, y, test_size=test_size, random_state=42
                )
            
            print(f"\n📊 Data split:")
            print(f"   Training samples: {X_train.shape[0]}")
            print(f"   Test samples: {X_test.shape[0]}")
            
            # Build and train pipeline
            print("\n🔄 Building model pipeline...")
            pipeline = self.build_pipeline()
            
            print("🔄 Training model...")
            pipeline.fit(X_train, y_train)
            print("✅ Model training completed!")
            
            # Evaluate model
            self.evaluate_model(pipeline, X_test, y_test)
            
            # Save model
            dump(pipeline, self.model_path)
            print(f"\n💾 Model saved to: {os.path.abspath(self.model_path)}")
            
            # Verify file was created
            if os.path.exists(self.model_path):
                file_size = os.path.getsize(self.model_path) / 1024  # Size in KB
                print(f"📁 Model file size: {file_size:.2f} KB")
                print("✅ Joblib file created successfully!")
                return True
            else:
                print("❌ Warning: Model file was not created!")
                return False
                
        except Exception as e:
            print(f"❌ Training failed: {e}")
            return False

    def check_sample_data(self):
        """Check what's in the sample data to understand the issue"""
        try:
            df = self.load_data()
            print("\n🔍 ANALYZING SAMPLE DATA:")
            print("=" * 40)
            
            # Show first few rows
            print("First 5 rows of data:")
            print(df.head().to_string())
            
            # Check eligibility criteria for each student
            print("\n🎯 APPLYING ELIGIBILITY CRITERIA:")
            for idx, row in df.iterrows():
                eligible = (
                    float(row['gpa']) <= 2.0 and 
                    float(row['exam_results']) >= 80 and 
                    int(row['number_of_dependents']) <= 2 and 
                    str(row['parent_employment_status']).strip().lower() == "unemployed"
                )
                status = "ELIGIBLE" if eligible else "NOT ELIGIBLE"
                print(f"Student {idx+1}: GPA={row['gpa']}, Exam={row['exam_results']}, "
                      f"Dependents={row['number_of_dependents']}, "
                      f"Employment={row['parent_employment_status']} → {status}")
            
            return True
        except Exception as e:
            print(f"❌ Error analyzing data: {e}")
            return False

if __name__ == "__main__":
    try:
        sm = ScholarshipModel()
        
        # First, analyze the data to see what's happening
        print("🔍 First, let's analyze your data...")
        sm.check_sample_data()
        
        print("\n" + "="*50)
        print("🎓 Now training the model...")
        print("="*50)
        
        # Then train the model
        success = sm.train()
        
        if success:
            print("\n✨ SCHOLARSHIP MODEL TRAINING COMPLETED SUCCESSFULLY!")
        else:
            print("\n❌ Model training failed. Check the issues above.")
            
    except Exception as e:
        print(f"❌ Program terminated with error: {e}")