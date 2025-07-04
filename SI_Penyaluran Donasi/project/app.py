from flask import Flask, render_template, request, redirect, url_for, flash
from config import Config
from models import db, User, Donation, AssistanceRequest

app = Flask(__name__)
app.config.from_object(Config)

# Initialize database
db.init_app(app)

# Home page
@app.route('/')
def index():
    return render_template('index.html')

# Donation form page
@app.route('/donation', methods=['GET', 'POST'])
def donation():
    if request.method == 'POST':
        donation_type = request.form['donation']
        amount = request.form.get('amount', type=float)
        description = request.form.get('description', '')
        user_id = 1  # Replace with session user ID

        donation = Donation(type=donation_type, amount=amount, description=description, user_id=user_id)
        db.session.add(donation)
        db.session.commit()
        flash("Donation submitted successfully!", "success")
        return redirect(url_for('index'))
    return render_template('donation.html')

# Register form page
@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        name = request.form['name']
        ktm = request.form['ktm']
        email = request.form['email']
        phone = request.form['phone']

        user = User(name=name, ktm=ktm, email=email, phone=phone)
        db.session.add(user)
        db.session.commit()
        flash("Registration successful!", "success")
        return redirect(url_for('index'))
    return render_template('register.html')

# Assistance request form page
@app.route('/pengajuan', methods=['GET', 'POST'])
def pengajuan():
    if request.method == 'POST':
        assistance_type = request.form['assistanceType']
        description = request.form['description']
        user_id = 1  # Replace with session user ID

        assistance_request = AssistanceRequest(assistance_type=assistance_type, description=description, user_id=user_id)
        db.session.add(assistance_request)
        db.session.commit()
        flash("Assistance request submitted successfully!", "success")
        return redirect(url_for('index'))
    return render_template('pengajuan.html')

if __name__ == '__main__':
    with app.app_context():
        db.create_all()  # Creates database tables
    app.run(debug=True)
