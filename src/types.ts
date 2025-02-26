export interface User {
    name: string,
    score: number,
    role: string
}

export interface Users {
    name: string,
    score: number,
    members: string[]
}

export interface itemRules {
    text: string,
    points: number,
    target: string
}

export interface Rules {
    title: string,
    items: itemRules[]
}

export interface ActionLog {
    id: number,
    username: string,
    text: string,
    points: number,
    target: string,
    typeAction: string,
    active: boolean
}

export interface popupType {
    type: string,
    action: ActionLog
}