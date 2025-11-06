/*
Template Name: Fabkin - Admin & Dashboard Template
Author: Pixeleyez
Website: https://pixeleyez.com/
Contact: pixeleyez@gmail.com
File: Project list init js
*/

document.addEventListener('DOMContentLoaded', function () {
    let statusChoice = document.getElementById('status-choice');
    if (statusChoice) {
        const choices = new Choices('#status-choice', {
            placeholderValue: 'Select Year',
            searchPlaceholderValue: 'Search...',
            removeItemButton: true,
            itemSelectText: 'Press to select',
        });
    }
    let statusChoice2 = document.getElementById('status-choice2');
    if (statusChoice2) {
        const choices = new Choices('#status-choice2', {
            placeholderValue: 'Select Unit Kerja',
            searchPlaceholderValue: 'Search...',
            removeItemButton: true,
            itemSelectText: 'Press to select',
        });

        choices.addEventListener('change', function() {
            window.location.href = 'realisasi.index';
        });
    }
    let priorityChoice = document.getElementById('priority-choice');
    if (priorityChoice) {
        const choices = new Choices('#priority-choice', {
            placeholderValue: 'Select Priority',
            searchPlaceholderValue: 'Search...',
            removeItemButton: true,
            itemSelectText: 'Press to select',
        });
    }
});