from flask import Flask, request, jsonify, abort
import joblib
import pandas as pd
import os

app = Flask(__name__)

MODEL_PATH = "scholarship_eligibility_model_rf.joblib"

# 🔹 Load model safely
model = None
if os.path.exists(MODEL_PATH):
    try:
        model = joblib.load(MODEL_PATH)
        print("✅ Model loaded successfully.")
    except Exception as e:
        print("⚠️ Model load failed:", e)
        model = None
else:
    print("⚠️ Model file not found; running with rules only if needed.")

# 🔹 Business rules (you can adjust thresholds)
def rule_based_decision(gpa, exam, dependents, parent_employment_status):
    """
    Rule:
      Eligible if:
        - gpa <= 2.5
        - exam >= 80
        - and (parent unemployed OR dependents >= 2)
    """
    pe = str(parent_employment_status).strip().lower()
    rule_met = (gpa <= 2.5) and (exam >= 80) and ((pe == "unemployed") or (dependents >= 2))
    rule_score = 0.95 if rule_met else 0.05
    return ("Eligible" if rule_met else "Not Eligible", rule_score)

# 🔹 Helper: safe model prediction
def get_model_prediction_and_proba(mdl, df):
    pred, proba = None, None
    try:
        pred = int(mdl.predict(df)[0])
    except Exception as e:
        print("❌ Model predict error:", e)

    if hasattr(mdl, "predict_proba"):
        try:
            probs = mdl.predict_proba(df)[0]
            proba = float(probs[1]) if len(probs) > 1 else float(probs[0])
        except Exception as e:
            print("❌ Model predict_proba error:", e)

    return pred, proba

@app.route("/predict", methods=["POST"])
def predict():
    try:
        data = request.get_json(force=True)
    except Exception:
        return jsonify({"status": "error", "message": "Invalid JSON."}), 400

    # 🔹 Required fields
    required = ["gpa", "exam_results", "parent_employment_status", "number_of_dependents"]
    missing = [f for f in required if f not in data]
    if missing:
        return jsonify({"status": "error", "message": f"Missing fields: {missing}"}), 400

    # 🔹 Parse inputs
    try:
        gpa = float(data["gpa"])
        exam = float(data["exam_results"])
        dependents = int(data["number_of_dependents"])
        parent_employment_status = str(data["parent_employment_status"]).strip().lower()
    except (ValueError, TypeError):
        return jsonify({"status": "error", "message": "Invalid input types."}), 400

    # 🔹 Decision mode (default = fallback override)
    use_rule = str(data.get("use_rule", "fallback")).strip().lower()
    if use_rule not in ("always", "fallback", "never"):
        use_rule = "fallback"

    input_df = pd.DataFrame([{
        "gpa": gpa,
        "exam_results": exam,
        "parent_employment_status": parent_employment_status,
        "number_of_dependents": dependents
    }])

    result, probability, source = None, None, None

    # 🔹 Rule-only mode
    if use_rule == "always" or model is None:
        result, probability = rule_based_decision(gpa, exam, dependents, parent_employment_status)
        source = "rule"

    else:
        # Model mode
        model_pred, model_proba = get_model_prediction_and_proba(model, input_df)

        if model_pred is None:
            # model failed -> rule fallback
            result, probability = rule_based_decision(gpa, exam, dependents, parent_employment_status)
            source = "rule-fallback"
        else:
            # base decision from model
            result = "Eligible" if model_pred == 1 else "Not Eligible"
            probability = float(model_proba) if model_proba is not None else (1.0 if model_pred == 1 else 0.0)
            source = "model"

            # 🔹 Fallback override: if model says Not Eligible but rule says Eligible, trust rule
            if use_rule == "fallback" and result == "Not Eligible":
                rule_result, rule_score = rule_based_decision(gpa, exam, dependents, parent_employment_status)
                if rule_result == "Eligible" and rule_score >= 0.9:
                    result = "Eligible"
                    probability = max(probability, rule_score)
                    source = "rule-override"

    # 🔹 Transparency (criteria summary)
    criteria = f"GPA={gpa}, Exam={exam}, Dependents={dependents}, Parent={parent_employment_status}"

    return jsonify({
        "status": "success",
        "result": result,
        "probability": round(float(probability), 3) if probability is not None else None,
        "source": source,
        "criteria": criteria
    })

@app.route("/health", methods=["GET"])
def health_check():
    return jsonify({
        "status": "healthy",
        "model_loaded": model is not None
    })

# Use this only for local testing
if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    print(f"🚀 Running locally at http://127.0.0.1:{port}")
    app.run(debug=True, host="0.0.0.0", port=port)